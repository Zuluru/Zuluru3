<?php
namespace App\Module;

use App\Controller\AppController;
use App\Model\Entity\Person;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

class ReportPeopleRetention extends Report{
	public function run($params, Person $recipient) {
		// We are interested in memberships
		$event_list = TableRegistry::getTableLocator()->get('Events')->find()
			// TODO: Fix or remove these hard-coded values
			->where(['event_type_id' => 1])
			->order(['open', 'close', 'id'])
			->indexBy('id')
			->toArray();

		$start = new FrozenDate("{$params['start']}-01-01");
		$end = new FrozenDate("{$params['end']}-12-31");

		$past_events = [];
		$registrations_table = TableRegistry::getTableLocator()->get('Registrations');
		foreach ($event_list as $event) {
			if ($event->membership_begins < $start || $event->membership_ends > $end) {
				unset($event_list[$event->id]);
				continue;
			}

			$people = $registrations_table->find()
				->select('person_id')
				->where([
					'event_id' => $event->id,
					'payment' => 'Paid',
				]);
			$event->counts = $registrations_table->find('list', ['keyField' => 'event_id', 'valueField' => 'registration_count'])
				->select(['event_id', 'registration_count' => $people->func()->count('DISTINCT person_id')])
				->where([
					'person_id IN' => $people,
					'event_id IN' => array_keys($event_list),
					'payment' => 'Paid',
				])
				->group(['event_id'])
				->toArray();

			if (!empty($past_events)) {
				$past_people = $registrations_table->find()
					->select('person_id')
					->where([
						'event_id IN' => $past_events,
						'payment' => 'Paid',
					]);
				$event->total = $registrations_table->find()
					->where([
						'event_id' => $event->id,
						'payment' => 'Paid',
						'person_id IN' => $past_people,
					])
					->count();
			} else {
				$event->total = 0;
			}

			$past_events[] = $event->id;
		}

		$people = $registrations_table->find()
			->select('person_id')
			->where([
				'Registrations.event_id IN' => array_keys($event_list),
				'Registrations.payment' => 'Paid',
			]);
		$member_list = $registrations_table->People->find()
			->where(['People.id IN' => $people])
			->order(['People.id'])
			->toArray();
		foreach ($member_list as $person) {
			// For large membership databases, this minimizes memory usage, increasing the chance that the report will run to completion
			gc_collect_cycles();

			$person->event_ids = $registrations_table->find()
				->where([
					'Registrations.person_id' => $person->id,
					'Registrations.event_id IN' => array_keys($event_list),
					'Registrations.payment' => 'Paid',
				])
				->extract('event_id')
				->toArray();
		}

		$fp = fopen('php://temp', 'r+');

		$header = [
			__('Event ID'),
			__('Membership Registration'),
		];
		foreach ($event_list as $event) {
			$header[] = $event->name;
		}
		fputcsv($fp, $header);

		$event_ids = collection($event_list)->extract('id')->toArray();
		$past_events = [];
		foreach ($event_list as $event) {
			$data = [
				$event->id,
				$event->name,
			];
			foreach ($event_ids as $event_id) {
				if ($event->id != $event_id && !in_array($event_id, $past_events)) {
					if (array_key_exists($event_id, $event->counts)) {
						$data[] = $event->counts[$event_id];
					} else {
						$data[] = 0;
					}
				} else {
					$data[] = '';
				}
			}

			// Output the data row
			fputcsv($fp, $data);
			$past_events[] = $event->id;
		}

		$data = [
			'',
			__('Total Prior'),
		];
		foreach ($event_list as $event) {
			$data[] = $event->total;
		}
		fputcsv($fp, $data);

		$data = [
			'',
			__('Total Registered'),
		];
		foreach ($event_list as $event) {
			$data[] = array_key_exists($event->id, $event->counts) ? $event->counts[$event->id] : 0;
		}
		fputcsv($fp, $data);

		$data = [
			'',
			__('% Prior'),
		];
		foreach ($event_list as $event) {
			$data[] = array_key_exists($event->id, $event->counts) ? sprintf('%2.2f', $event->total * 100 / $event->counts[$event->id]) : 0;
		}
		fputcsv($fp, $data);

		fputcsv($fp, []);

		$column = Configure::read('gender.column');
		foreach ($member_list as $person) {
			$data = [
				$person->$column,
				$person->birthdate ? $person->birthdate->year : '',
			];

			foreach ($event_list as $event) {
				$data[] = in_array($event->id, $person->event_ids) ? 1 : 0;
			}
			fputcsv($fp, $data);
		}

		rewind($fp);
		$csv = stream_get_contents($fp);
		fclose($fp);

		AppController::_sendMail([
			'to' => $recipient,
			'subject' => function() { return __('{0} Retention Report', Configure::read('organization.short_name')); },
			'content' => __('Please find attached your retention report for {0} to {1}.', $params['start'], $params['end']),
			'sendAs' => 'text',
			'attachments' => [
				'Retention.csv' => [
					'data' => $csv,
					'mimetype' => 'text/csv',
				]
			],
		]);
	}
}
