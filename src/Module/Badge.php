<?php

namespace App\Module;

use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Core\ModuleRegistry;
use App\Exception\MissingModuleException;
use App\Model\Entity\BadgesPerson;

class Badge {
	public $visibility = [];

	public function visibility(IdentityInterface $identity = null, $min_visibility = BADGE_VISIBILITY_LOW) {
		$this->visibility = range(BADGE_VISIBILITY_HIGH, $min_visibility);
		if ($identity && $identity->isManager()) {
			$this->visibility[] = BADGE_VISIBILITY_ADMIN;
		}
		return $this->visibility;
	}

	public function getVisibility() {
		return $this->visibility;
	}

	public function prepForDisplay($person, $categories = ['runtime', 'aggregate']) {
		foreach ($categories as $category) {
			$badges = TableRegistry::get('Badges')->find()
				->where([
					'Badges.category' => $category,
					'Badges.active' => true,
					'Badges.visibility IN' => $this->visibility,
				])
				->toArray();

			foreach ($badges as $badge) {
				if (!empty($badge->handler)) {
					if ($category == 'aggregate') {
						list($id,$reps) = explode('x', $badge->handler);
						$this->aggregate($badge, $person, null, $id, $reps);
					} else {
						try {
							$handler = ModuleRegistry::getInstance()->load("Badge:{$badge->handler}");
							$this->$category($badge, $person, null, $handler);
						} catch (MissingModuleException $ex) {
							// TODO: Graceful handling of missing handlers
						}
					}
				}
			}
		}
	}

	/**
	 * Find all badges of a particular category and determines whether or not each one applies to the provided
	 * record, assigning or removing badges as required.
	 *
	 * @param mixed $category The badge category to test.
	 * @param mixed $data The record to test badges against. Specifics depend on the badge category.
	 * @param mixed $extra Some categories require additional data to work (e.g. payment status for registrations).
	 * @return mixed True if there were no failures, false otherwise.
	 *
	 */
	public function update($category, $data, $extra = null) {
		$success = true;

		$badges = TableRegistry::get('Badges')->find()
			->where([
				'Badges.category' => $category,
				'Badges.active' => true,
			]);

		foreach ($badges as $badge) {
			if (!empty($badge->handler)) {
				try {
					$handler = ModuleRegistry::getInstance()->load("Badge:{$badge->handler}");
					$success &= $this->$category($badge, $data, $extra, $handler);
				} catch (MissingModuleException $ex) {
					// TODO: Graceful handling of missing handlers
				}
			}
		}

		return $success;
	}

	/**
	 * The various badge categories use their handlers in different ways.
	 * Nominated and assigned categories are handled manually, so no callback is required for them.
	 */

	public function runtime($badge, $person, $extra, $handler) {
		if ($handler->applicable($person)) {
			$person->badges[] = $badge;
		}
		return true;
	}

	public function aggregate($badge, $person, $extra, $id, $reps) {
		if (!$person->has('badges')) {
			trigger_error('TODOTESTING', E_USER_ERROR);
		}

		$badges = collection($person->badges)->match(['id' => $id])->toList();
		if (count($badges) >= $reps) {
			$person->badges = collection($person->badges)->reject(function ($badge, $key) use ($id) {
				return $badge->id == $id;
			})->toList();
			$person->badges[] = $badge;
		}
	}

	public function game($badge, $game, $extra, $handler) {
		$success = true;

		foreach ([$game->home_team, $game->away_team] as $team) {
			TableRegistry::get('BadgesPeople')->deleteAll([
				'badge_id' => $badge->id,
				'team_id' => $team->id,
				'game_id' => $game->id,
			]);
			if ($handler->applicable($game, $team->id)) {
				foreach ($team->roster as $person) {
					$person->_joinData = new BadgesPerson([
						'team_id' => $team->id,
						'game_id' => $game->id,
						'approved' => true,
					]);
					$success &= TableRegistry::get('Badges')->People->link($badge, [$person]);
				}
			}
		}

		return $success;
	}

	public function team($badge, $roster, $person, $handler) {
		if (!is_a($roster, 'App\Model\Entity\TeamsPerson')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		if (!isset($this->team) || $this->team->id != $roster->team_id) {
			$this->team = TableRegistry::get('Teams')->get($roster->team_id, [
				'contain' => ['Divisions'],
			]);
		}

		$success = true;

		TableRegistry::get('BadgesPeople')->deleteAll([
			'badge_id' => $badge->id,
			'person_id' => $roster->person_id,
			'team_id' => $roster->team_id,
		]);
		if ($handler->applicable($this->team) && in_array($roster->role, Configure::read('regular_roster_roles')) && $roster->status == ROSTER_APPROVED) {
			$person->_joinData = new BadgesPerson([
				'team_id' => $roster->team_id,
				'approved' => true,
			]);
			$success &= TableRegistry::get('Badges')->People->link($badge, [$person]);
		}

		return $success;
	}

	public function registration($badge, $registration, $paid, $handler) {
		if (!is_a($registration, 'App\Model\Entity\Registration')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		if (!$registration->has('person')) {
			TableRegistry::get('Registrations')->loadInto($registration, ['People']);
		}
		if (!$registration->has('event')) {
			TableRegistry::get('Registrations')->loadInto($registration, ['Events' => ['EventTypes']]);
		}

		$success = true;

		TableRegistry::get('BadgesPeople')->deleteAll([
			'badge_id' => $badge->id,
			'registration_id' => $registration->id,
		]);

		if ($paid && $handler->applicable($registration->event)) {
			$registration->person->_joinData = new BadgesPerson([
				'registration_id' => $registration->id,
				'approved' => true,
			]);
			$success &= TableRegistry::get('Badges')->People->link($badge, [$registration->person]);
		}

		return $success;
	}

}
