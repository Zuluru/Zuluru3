<?php
namespace App\Shell\Task;

use App\Controller\AppController;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * MembershipLetters Task
 */
class MembershipLettersTask extends Shell {

	public function main() {
		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		if (!Configure::read('feature.registration')) {
			return;
		}

		$people_table = TableRegistry::get('People');
		$logs_table = TableRegistry::get('ActivityLogs');

		$events = $people_table->Registrations->Events->find()
			->matching('EventTypes', function (Query $q) {
				return $q->where(['type' => 'membership']);
			});

		foreach ($events as $event) {
			if ($event->has('membership_begins') &&
				$event->membership_begins->isPast() &&
				$event->membership_ends->isFuture()
			) {
				$year = $event->membership_begins->year;
				$people = $people_table->find()
					->contain([Configure::read('Security.authModel')])
					// TODO: Use subquery objects for these
					->where([
						["People.id IN (SELECT DISTINCT person_id FROM registrations WHERE event_id = {$event->id} AND payment = 'Paid')"],
						["People.id NOT IN (SELECT person_id FROM activity_logs WHERE type = 'email_membership_letter' AND custom = {$event->membership_begins->year})"],
					])
					->limit(100);

				foreach ($people as $person) {
					if (AppController::_sendMail([
						'to' => $person,
						'subject' => __('{0} {1} Membership', Configure::read('organization.name'), $event->membership_begins->year),
						'template' => 'membership_letter',
						'sendAs' => 'both',
						'header' => [
							'Auto-Submitted' => 'auto-generated',
							'X-Auto-Response-Suppress' => 'OOF',
						],
						'viewVars' => compact('event', 'year', 'person'),
					])) {
						// Update the activity log
						$logs_table->save($logs_table->newEntity([
							'type' => 'email_membership_letter',
							'custom' => $event->membership_begins->year,
							'person_id' => $person->id,
						]));
					}
				}
			}
		}
	}

}
