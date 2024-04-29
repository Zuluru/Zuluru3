<?php
namespace App\Shell\Task;

use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Middleware\ConfigurationLoader;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * MembershipBadges Task
 */
class MembershipBadgesTask extends Shell {

	public function main() {
		ConfigurationLoader::loadConfiguration();
		if (!Configure::read('feature.registration') || !Configure::read('feature.badges')) {
			return;
		}

		$badges = TableRegistry::getTableLocator()->get('Badges')->find()
			->where([
				'Badges.category' => 'registration',
				'Badges.active' => true,
			]);
		if ($badges->all()->isEmpty()) {
			return;
		}

		$badge_obj = ModuleRegistry::getInstance()->load('Badge');
		$events_table = TableRegistry::getTableLocator()->get('Events');
		$logs_table = TableRegistry::getTableLocator()->get('ActivityLogs');
		$today = FrozenDate::now();

		// Find all membership events for which the membership has started,
		// but we haven't opened it. The only ones that can possibly be
		// opened are ones that are closed, but not even all of those will be.
		$events = $events_table->find()
			->contain(['EventTypes'])
			->where([
				'Events.open <=' => FrozenDate::now(),
				'EventTypes.type' => 'membership',
			]);

		$opened = $logs_table->find('list', [
			'conditions' => ['type' => 'membership_opened'],
			'keyFields' => 'id',
			'valueField' => 'custom',
		]);
		if (!$opened->all()->isEmpty()) {
			$events->andWhere(['Events.id NOT IN' => $opened->toArray()]);
		}

		// Add membership badges for each of those events.
		foreach ($events as $event) {
			if ($event->membership_begins <= $today) {
				// TODO: This method is super slow. Any way to improve on it?
				$events_table->loadInto($event, [
					'Registrations' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Registrations.payment IN' => Configure::read('registration_paid')]);
						},
						'People',
					],
				]);
				foreach ($event->registrations as $registration) {
					$registration->event = $event;
					// We are only dealing with paid and pending registrations, so the $extra parameter is true
					$badge_obj->update('registration', $registration, true);
				}
				$logs_table->save($logs_table->newEntity(['type' => 'membership_opened', 'custom' => $event->id]));
			}
		}

		// Find all membership events for which the membership has ended,
		// but we haven't closed it. The only ones that can possibly be
		// ended are ones that are closed, but not even all of those will be.
		$events = $events_table->find()
			->contain(['EventTypes'])
			->where([
				'Events.close <' => FrozenDate::now(),
				'EventTypes.type' => 'membership',
			]);

		$closed = $logs_table->find('list', [
			'conditions' => ['type' => 'membership_closed'],
			'keyFields' => 'id',
			'valueField' => 'custom',
		]);
		if (!$closed->all()->isEmpty()) {
			$events->andWhere(['Events.id NOT IN' => $closed->toArray()]);
		}

		foreach ($events as $event) {
			if ($event->membership_ends <= $today) {
				$events_table->loadInto($event, [
					'Registrations' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Registrations.payment IN' => Configure::read('registration_paid')]);
						},
						'People',
					],
				]);
				foreach ($event->registrations as $registration) {
					$registration->event = $event;
					// We are only dealing with paid and pending registrations, so the $extra parameter is true
					$badge_obj->update('registration', $registration, true);
				}
				$logs_table->save($logs_table->newEntity(['type' => 'membership_closed', 'custom' => $event->id]));
			}
		}
	}

}
