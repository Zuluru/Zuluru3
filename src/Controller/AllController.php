<?php
namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class AllController extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		return ['language', 'credits'];
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'splash',
				'consolidated_schedule',
			])) {
				return true;
			}

			// People can perform these operations on their own account
			if (in_array($this->request->params['action'], [
				'schedule',
			]))
			{
				// If a player id is specified, check if it's the logged-in user, or a relative
				// If no player id is specified, it's always the logged-in user
				$person = $this->request->query('person');
				$relatives = $this->UserCache->read('RelativeIDs');
				if (!$person || $person == $this->UserCache->currentId() || in_array($person, $relatives)) {
					return true;
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	public function splash() {
		if ($this->UserCache->read('Person.status') != 'locked') {
			$relatives = collection($this->UserCache->read('Relatives'))->match(['_joinData.approved' => 1])->toList();
		} else {
			$relatives = [];
		}

		if (Configure::read('feature.affiliates') && $this->UserCache->read('Person.status') != 'locked') {
			$affiliates_table = TableRegistry::get('Affiliates');
			$affiliates = $affiliates_table->find('active')->indexBy('id')->toArray();
			if (Configure::read('Perm.is_admin')) {
				$unmanaged = $affiliates_table->find('active')
					->contain([
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['AffiliatesPeople.position' => 'manager']);
							},
						],
					])
					->toArray();
				foreach ($unmanaged as $key => $affiliate) {
					if (!empty($affiliate->people)) {
						unset($unmanaged[$key]);
					} else {
						unset($unmanaged[$key]->people);
					}
				}
			}
		} else {
			$affiliates = [];
		}

		$applicable_affiliates = $this->_applicableAffiliateIDs();

		$this->set(compact('relatives', 'affiliates', 'unmanaged', 'applicable_affiliates'));
	}

	public function schedule() {
		$id = $this->request->query('person');
		if (!$id) {
			$id = $this->UserCache->currentId();
		}

		// This intentionally checks the current user's status, not the person whose data is being loaded
		if ($this->UserCache->read('Person.status') != 'locked') {
			$teams = $this->UserCache->read('Teams', $id);
			$team_ids = $this->UserCache->read('TeamIDs', $id);
			$items = $this->_schedule([$id], $team_ids);
		}

		$this->set(compact('id', 'items', 'teams', 'team_ids'));
	}

	private function _schedule($people, $team_ids) {
		if (!empty($team_ids)) {
			$limit = max(4, ceil(count(array_unique($team_ids)) * 1.5));
			$games_table = TableRegistry::get('Games');
			$past = $games_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->contain([
					'Divisions' => ['Days', 'Leagues'],
					'ScoreEntries' => [
						'queryBuilder' => function (Query $q) use ($team_ids) {
							return $q->where(['ScoreEntries.team_id IN' => $team_ids]);
						}
					],
				])
				->where([
					'Games.published' => true,
					'GameSlots.game_date <' => FrozenDate::now(),
					'GameSlots.game_date >=' => FrozenDate::now()->subWeeks(2),
				])
				->order(['GameSlots.game_date DESC', 'GameSlots.game_start DESC'])
				->limit($limit)
				->toArray();
			$past = array_reverse($past);

			$future = $games_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->contain([
					'Divisions' => ['Days', 'Leagues'],
				])
				->where([
					'Games.published' => true,
					'GameSlots.game_date >=' => FrozenDate::now(),
					'GameSlots.game_date <' => FrozenDate::now()->addWeeks(2),
				])
				->order(['GameSlots.game_date', 'GameSlots.game_start'])
				->limit($limit)
				->toArray();

			// Check if we need to update attendance records for any upcoming games
			$reread = false;
			foreach (array_merge($past, $future) as $game) {
				// TODO: This test won't actually be sufficient in the case where a relative has their attendance record,
				// but this person was just added to the team. Ideal solution will eliminate all this crud and the reread
				// block below too. And all the same for the team events further down.
				if (empty($game->attendances)) {
					if (!empty($game->home_team->id) && $game->home_team->track_attendance && in_array($game->home_team->id, $team_ids)) {
						$games_table->readAttendance($game->home_team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);
						$reread = true;
					}
					if (!empty($game->away_team->id) && $game->away_team->track_attendance && in_array($game->away_team->id, $team_ids)) {
						$games_table->readAttendance($game->away_team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);
						$reread = true;
					}
				}
			}

			if ($reread) {
				$past = $games_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->contain([
						'Divisions' => ['Days', 'Leagues'],
					])
					->where([
						'Games.published' => true,
						'GameSlots.game_date <' => FrozenDate::now(),
						'GameSlots.game_date >=' => FrozenDate::now()->subWeeks(2),
					])
					->order(['GameSlots.game_date DESC', 'GameSlots.game_start DESC'])
					->limit($limit)
					->toArray();
				$past = array_reverse($past);

				$future = $games_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->contain([
						'Divisions' => ['Days', 'Leagues'],
					])
					->where([
						'Games.published' => true,
						'GameSlots.game_date >=' => FrozenDate::now(),
						'GameSlots.game_date <' => FrozenDate::now()->addWeeks(2),
					])
					->order(['GameSlots.game_date', 'GameSlots.game_start'])
					->limit($limit)
					->toArray();
			}

			$items = array_merge($past, $future);

			$events_table = TableRegistry::get('TeamEvents');
			$past = $events_table->find('schedule', ['teams' => $team_ids])
				->where([
					'TeamEvents.date <' => FrozenDate::now(),
					'TeamEvents.date >=' => FrozenDate::now()->subWeeks(2),
				])
				->order(['TeamEvents.date DESC', 'TeamEvents.start DESC'])
				->limit($limit)
				->toArray();
			$past = array_reverse($past);

			$future = $events_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->where([
					'TeamEvents.date >=' => FrozenDate::now(),
					'TeamEvents.date <' => FrozenDate::now()->addWeeks(2),
				])
				->order(['TeamEvents.date', 'TeamEvents.start'])
				->limit($limit)
				->toArray();

			// Check if we need to update attendance records for any upcoming events
			$reread = false;
			foreach ($future as $team_event) {
				if ($team_event->team->track_attendance && empty($team_event->attendances)) {
					$events_table->readAttendance($team_event->team_id, $team_event->id);
					$reread = true;
				}
			}

			if ($reread) {
				$future = $events_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->where([
						'TeamEvents.date >=' => FrozenDate::now(),
						'TeamEvents.date <' => FrozenDate::now()->addWeeks(2),
					])
					->order(['TeamEvents.date', 'TeamEvents.start'])
					->limit($limit)
					->toArray();
			}

			$items = array_merge($items, $past, $future);
		} else {
			$items = [];
		}

		if (Configure::read('feature.tasks')) {
			foreach ($people as $id) {
				$tasks = $this->UserCache->read('Tasks', $id);
				if (!empty($tasks)) {
					$items = array_merge($items, $tasks);
				}
			}
		}

		usort($items, ['App\Model\Table\GamesTable', 'compareDateAndField']);
		return $items;
	}

	public function consolidated_schedule() {
		// We need to read attendance for all relatives, as shared games might not
		// be on everyone's list, but we still want to accurately show attendance
		if ($this->UserCache->read('Person.status') != 'locked') {
			$people = $this->UserCache->read('RelativeIDs');
			$relatives = collection($this->UserCache->read('Relatives'))->match(['_joinData.approved' => 1])->toList();
		} else {
			$people = $relatives = [];
		}

		$id = $this->UserCache->currentId();
		array_unshift($people, $id);

		$teams = [];
		foreach ($people as $person_id) {
			$teams = array_merge($teams, $this->UserCache->read('Teams', $person_id));
		}
		$teams = collection($teams)->indexBy('id')->toArray();
		$team_ids = array_keys($teams);

		$items = $this->_schedule($people, $team_ids);
		$this->set(compact('id', 'items', 'relatives', 'teams', 'team_ids'));
	}

	public function clear_cache() {
		Cache::clear(false, 'long_term');
		$this->Flash->success(__('The cache has been cleared.'));
		return $this->redirect('/');
	}

	public function language() {
		$lang = $this->request->query('lang');
		if (!empty($lang)) {
			$this->request->session()->write('Config.language', $lang);
			if (Configure::read('Perm.is_logged_in')) {
				I18n::locale($lang);
				$this->Flash->html(__('Your language has been changed for this session. To change it permanently, {0}.'), [
					'params' => [
						'replacements' => [
							[
								'type' => 'link',
								'link' => __('update your preferences'),
								'target' => ['controller' => 'People', 'action' => 'preferences'],
							],
						],
					],
				]);
			}
		}
		return $this->redirect('/');
	}

	public function credits() {
	}

}
