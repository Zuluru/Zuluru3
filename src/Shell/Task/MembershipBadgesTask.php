<?php
namespace App\Shell\Task;

use App\Controller\AppController;
use App\Core\ModuleRegistry;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * MembershipBadges Task
 */
class MembershipBadgesTask extends Shell {

	public function main() {
		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		if (!Configure::read('feature.registration') || !Configure::read('feature.badges')) {
			return;
		}

		$badges = TableRegistry::get('Badges')->find()
			->where([
				'Badges.category' => 'registration',
				'Badges.active' => true,
			]);
		if ($badges->isEmpty()) {
			return;
		}

		$badge_obj = ModuleRegistry::getInstance()->load('Badge');
		$events_table = TableRegistry::get('Events');
		$logs_table = TableRegistry::get('ActivityLogs');
		$today = FrozenDate::now();

		// Find all membership events for which the membership has started,
		// but we haven't opened it. The only ones that can possibly be
		// opened are ones that are closed, but not even all of those will be.
		// TODO: Improve this query
		$events = $events_table->find()
			->where([
				'open <=' => FrozenDate::now(),
				'affiliate_id IN' => AppController::_applicableAffiliateIDs(),
				'event_type_id IN' => $events_table->EventTypes->find('list', [
					'conditions' => ['type' => 'membership'],
					'keyFields' => 'id',
					'valueField' => 'id',
				]),
			]);

		$opened = $logs_table->find('list', [
			'conditions' => ['type' => 'membership_opened'],
			'keyFields' => 'id',
			'valueField' => 'custom',
		]);
		if (!$opened->isEmpty()) {
			$events->andWhere(['NOT' => ['id IN' => $opened->toArray()]]);
		}

		// Add membership badges for each of those events.
		foreach ($events as $event) {
			if ($event->membership_begins <= $today) {
				// TODO: This method is super slow. Any way to improve on it?
				$events_table->loadInto($event, [
					'EventTypes',
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
			->where([
				'close <' => FrozenDate::now(),
				'affiliate_id IN' => AppController::_applicableAffiliateIDs(),
				'event_type_id IN' => $events_table->EventTypes->find('list', [
					'conditions' => ['type' => 'membership'],
					'keyFields' => 'id',
					'valueField' => 'id',
				]),
			]);

		$closed = $logs_table->find('list', [
			'conditions' => ['type' => 'membership_closed'],
			'keyFields' => 'id',
			'valueField' => 'custom',
		]);
		if (!$closed->isEmpty()) {
			$events->andWhere(['NOT' => ['id IN' => $closed->toArray()]]);
		}

		foreach ($events as $event) {
			if ($event->membership_ends <= $today) {
				$events_table->loadInto($event, [
					'EventTypes',
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
