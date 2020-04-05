<?php
namespace App\Module;

use App\Controller\AppController;
use App\Model\Entity\Person;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class ReportPeopleParticipation extends Report {
	public function run($params, Person $recipient) {
		$events_table = TableRegistry::get('Events');

		// Initialize the data structures
		$participation = [];
		$pos = ['captain' => 0, 'player' => 0];
		$seasons = array_fill_keys(Configure::read('options.season'), [
			'season' => $pos,
			'tournament' => $pos,
		]);
		$years = array_fill_keys(range($params['start'], $params['end']), $seasons);

		$seasons_found = array_fill_keys(Configure::read('options.season'), [
			'season' => false,
			'tournament' => false,
		]);

		$captains = Configure::read('privileged_roster_roles');

		$membership_event_list = TableRegistry::get('Events')->find()
			// TODO: Fix or remove these hard-coded values
			->where(['event_type_id' => 1])
			->order(['open', 'close', 'id'])
			->indexBy('id')
			->toArray();
		$event_names = [];

		for ($year = $params['start']; $year <= $params['end']; ++ $year) {
			$start = new FrozenDate("{$year}-01-01");
			$end = new FrozenDate("{$year}-12-31");

			// We are interested in teams in divisions that operated this year
			$divisions = TableRegistry::get('Divisions')->find()
				->contain([
					'Teams' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('playing_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'Leagues',
				])
				->where([
					function (QueryExpression $exp) use ($start, $end) {
						return $exp->between('Divisions.open', $start, $end, 'date');
					},
				]);

			// Consolidate the team data into the person-based array
			foreach ($divisions as $division) {
				foreach ($division->teams as $team) {
					foreach ($team->people as $person) {
						if (!array_key_exists($person->id, $participation)) {
							$participation[$person->id] = $person;
							$participation[$person->id]->events = [];
							$participation[$person->id]->divisions = $years;
						}

						if ($division->schedule_type == 'tournament') {
							$key = 'tournament';
						} else {
							$key = 'season';
						}
						if (in_array($person->_joinData->role, $captains)) {
							$pos = 'captain';
						} else {
							$pos = 'player';
						}
						++ $participation[$person->id]->divisions[$year][$division->league->season][$key][$pos];
						$seasons_found[$division->league->season][$key] = true;
					}
				}
			}

			// These arrays get big, and we don't need team data any more
			unset($divisions);

			// We are interested in memberships that covered this year
			$membership_event_ids = [];
			foreach ($membership_event_list as $event) {
				if ($event->membership_begins >= $start &&
					$event->membership_ends <= $end)
				{
					$event_names[$event->id] = $event->name;
					$membership_event_ids[] = $event->id;
				}
			}

			// We are interested in some other registration events that closed this year
			$conditions = [
				function (QueryExpression $exp) use ($start, $end) {
					return $exp->between('Events.close', $start, $end->addYear(), 'date');
				},
				// TODO: Fix or remove these hard-coded values
				'Events.event_type_id IN' => [5,6,7],
			];
			if (!empty($membership_event_ids)) {
				$conditions = ['OR' => [
					'Events.id IN' => $membership_event_ids,
					$conditions,
				]];
			}

			$events = $events_table->find()
				->contain([
					'Registrations' => [
						'People',
						'queryBuilder' => function (Query $q) {
							return $q->where(['payment' => 'Paid']);
						},
					],
				])
				->where($conditions)
				->order(['Events.event_type_id', 'Events.open', 'Events.close', 'Events.id']);

			// Consolidate the registrations into the person-based array
			foreach ($events as $event) {
				$event_names[$event->id] = $event->name;
				foreach ($event->registrations as $registration) {
					if (!array_key_exists($registration->person_id, $participation)) {
						$participation[$registration->person_id] = $registration->person;
						$participation[$registration->person_id]->events = [];
						$participation[$registration->person_id]->divisions = $years;
					}
					$participation[$registration->person_id]->events[$event->id] = true;
				}
			}

			// These arrays get big, and we don't need event data any more
			unset($events);
		}

		usort($participation, ['App\Model\Table\PeopleTable', 'comparePerson']);

		$fp = fopen('php://temp', 'r+');

		$header = [
			__('User ID'),
			__('First Name'),
			__('Last Name'),
			Configure::read('gender.label'),
			__('Birthdate'),
			__('City'),
		];
		for ($year = $params['start']; $year <= $params['end']; ++ $year) {
			foreach ($seasons_found as $name => $season) {
				if ($season['season']) {
					$header[] = $year . ' ' . __($name) . ' ' . __('captain');
					$header[] = $year . ' ' . __($name) . ' ' . __('player');
				}
				if ($season['tournament']) {
					$header[] = $year . ' ' . __($name) . ' ' . __('tournament') . ' ' . __('captain');
					$header[] = $year . ' ' . __($name) . ' ' . __('tournament') . ' ' . __('player');
				}
			}
		}
		foreach ($event_names as $event) {
			$header[] = $event;
		}
		fputcsv($fp, $header);

		$columm = Configure::read('gender.column');
		foreach ($participation as $person) {
			$data = [
				$person->id,
				$person->first_name,
				$person->last_name,
				$person->$columm,
				$person->birthdate,
				$person->addr_city,
			];
			for ($year = $params['start']; $year <= $params['end']; ++ $year) {
				foreach ($seasons_found as $name => $season) {
					if ($season['season']) {
						$data[] = $person->divisions[$year][$name]['season']['captain'];
						$data[] = $person->divisions[$year][$name]['season']['player'];
					}
					if ($season['tournament']) {
						$data[] = $person->divisions[$year][$name]['tournament']['captain'];
						$data[] = $person->divisions[$year][$name]['tournament']['player'];
					}
				}
			}
			foreach (array_keys($event_names) as $event) {
				$data[] = array_key_exists($event, $person->events) ? 1 : '';
			}

			// Output the data row
			fputcsv($fp, $data);
		}

		rewind($fp);
		$csv = stream_get_contents($fp);
		fclose($fp);
		pr($csv);

		AppController::_sendMail([
			'to' => $recipient,
			'subject' => function() { return __('{0} Participation Report', Configure::read('organization.short_name')); },
			'content' => __('Please find attached your participation report for {0} to {1}.', $params['start'], $params['end']),
			'sendAs' => 'text',
			'attachments' => [
				'Participation.csv' => [
					'data' => $csv,
					'mimetype' => 'text/csv',
				]
			],
		]);
	}

}
