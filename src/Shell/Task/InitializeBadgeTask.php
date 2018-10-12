<?php
namespace App\Shell\Task;

use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Exception\MissingModuleException;
use App\Model\Entity\BadgesPerson;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * RunReport Task
 *
 * @property \App\Model\Table\BadgesTable $badges_table
 */
class InitializeBadgeTask extends Shell {

	public function main() {
		try {
			$event = new CakeEvent('Configuration.initialize', $this);
			EventManager::instance()->dispatch($event);

			$badges_table = TableRegistry::get('Badges');
			$badge = $badges_table->find()
				->where(['refresh_from >' => 0])
				->first();
			if (!$badge) {
				return;
			}

			TableRegistry::get('Configuration')->loadAffiliate($badge->affiliate_id);

			$badges_table->connection()->transactional(function () use ($badge) {
				try {
					$handler = ModuleRegistry::getInstance()->load("Badge:{$badge->handler}");
				} catch (MissingModuleException $ex) {
					AppController::_sendMail([
						'to' => 'admin@zuluru.org',
						'subject' => __('{0} Badge Initialization Failed', Configure::read('organization.short_name')),
						'content' => __('Failed to load the module for the "{0}" badge.', $badge->name) . ' ' .
							__('This is a fatal error, it will not be retried.'),
						'sendAs' => 'text',
					]);

					return false;
				}

				$deletions = $additions = [];
				$badges_people_table = TableRegistry::get('BadgesPeople');
				// Try to keep this from running for more than about a minute
				$abort_time = time() + 45;

				switch ($badge->category) {
					case 'team':
						// We don't contain People here, because that would read the roster for all teams all at once,
						// using massive memory. Instead, we lazy load the rosters on a team-by-team basis.
						$teams = TableRegistry::get('Teams')->find()
							->where(['Teams.id >=' => $badge->refresh_from])
							->contain(['Divisions'])
							->order(['Teams.id'])
							->limit(5000);
						foreach ($teams as $team) {
							if (time() > $abort_time) {
								break;
							}

							$deletions[] = $team->id;

							if ($handler->applicable($team)) {
								foreach ($team->roster as $person) {
									$additions[] = new BadgesPerson([
										'person_id' => $person->id,
										'badge_id' => $badge->id,
										'team_id' => $team->id,
										'approved' => true,
									]);
								}
							}

							// For large databases, this minimizes memory usage
							gc_collect_cycles();
						}

						if (!empty($deletions)) {
							// Delete existing badges
							$badges_people_table->deleteAll([
								'badge_id' => $badge->id,
								'team_id >=' => min($deletions),
								'team_id <=' => max($deletions),
							]);
						}

						break;

					case 'game':
						// We don't contain People here, because that would read the roster for all teams all at once,
						// using massive memory. Instead, we lazy load the rosters on a team-by-team basis.
						$games = TableRegistry::get('Games')->find()
							->where(['Games.id >=' => $badge->refresh_from])
							->contain(['HomeTeam', 'AwayTeam'])
							->order(['Games.id'])
							->limit(5000);
						foreach ($games as $game) {
							if (time() > $abort_time) {
								break;
							}

							$deletions[] = $game->id;

							foreach ([$game->home_team, $game->away_team] as $team) {
								if ($team && $handler->applicable($game, $team->id)) {
									foreach ($team->roster as $person) {
										$additions[] = new BadgesPerson([
											'person_id' => $person->id,
											'badge_id' => $badge->id,
											'team_id' => $team->id,
											'game_id' => $game->id,
											'approved' => true,
										]);
									}
								}
							}

							// For large databases, this minimizes memory usage
							gc_collect_cycles();
						}

						if (!empty($deletions)) {
							// Delete existing badges
							$badges_people_table->deleteAll([
								'badge_id' => $badge->id,
								'game_id >=' => min($deletions),
								'game_id <=' => max($deletions),
							]);
						}

						break;

					case 'registration':
						$i = 0;

						// We don't contain Registrations here, because that would read the list for all events all at once,
						// using massive memory. Instead, we lazy load the registrations on an event-by-event basis.
						$events = TableRegistry::get('Events')->find()
							->where(['Events.id >=' => $badge->refresh_from])
							->contain(['EventTypes'])
							->order(['Events.id'])
							->limit(5000);
						foreach ($events as $event) {
							if (time() > $abort_time) {
								break;
							}

							// This is used for tracking which event to do next
							$deletions[] = $event->id;

							$event_deletions = [];
							if ($handler->applicable($event)) {
								foreach ($event->people as $person) {
									$event_deletions[] = $person->_matchingData['Registrations']->id;

									$additions[] = new BadgesPerson([
										'person_id' => $person->id,
										'badge_id' => $badge->id,
										'registration_id' => $person->_matchingData['Registrations']->id,
										'approved' => true,
									]);

									if (++$i == 100) {
										// For large databases, this minimizes memory usage
										gc_collect_cycles();
										$i = 0;
									}
								}
							} else {
								$event_deletions = TableRegistry::get('Registrations')->find()
									->select('id')
									->where(['event_id' => $event->id])
									->extract('id')
									->toArray();
							}

							// Do this one inside the loop, the keep the number of deletions down
							if (!empty($event_deletions)) {
								// Delete existing badges
								$badges_people_table->deleteAll([
									'badge_id' => $badge->id,
									'registration_id IN (' . implode(',', $event_deletions) . ')',
								]);
							}
						}

						break;

					default:
						AppController::_sendMail([
							'to' => 'admin@zuluru.org',
							'subject' => __('{0} Badge Initialization Failed', Configure::read('organization.short_name')),
							'content' => __('Unrecognized badge category "{0}".', __($badge->category)) . ' ' .
								__('This is a fatal error, it will not be retried.'),
							'sendAs' => 'text',
						]);

						return false;
				}

				$success = empty($additions) || $badges_people_table->saveMany($additions);

				if (!$success) {
					AppController::_sendMail([
						'to' => 'admin@zuluru.org',
						'subject' => __('{0} Badge Initialization Failed', Configure::read('organization.short_name')),
						'content' => __('Failed to initialize "{0}" badge.', $badge->name) . ' ' .
							__('This is a fatal error, it will not be retried.'),
						'sendAs' => 'text',
					]);
				}

				if (!empty($deletions)) {
					$badge->refresh_from = max($deletions) + 1;
				} else {
					$badge->refresh_from = 0;
				}

				return $success;
			});

			$badges_table->save($badge);
		} catch (\Exception $ex) {
			AppController::_sendMail([
				'to' => 'admin@zuluru.org',
				'subject' => __('{0} Badge Initialization Failed', Configure::read('organization.short_name')),
				'content' => __('Caught unexpected exception "{0}" while initializing the "{1}" badge.', $ex->getMessage(), $badge->name) . ' ' .
					__('This is a fatal error, it will not be retried.'),
				'sendAs' => 'text',
			]);
		}
	}

}
