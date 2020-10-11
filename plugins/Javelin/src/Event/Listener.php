<?php
/**
 * Implementation of Javelin event listeners.
 */

namespace Javelin\Event;

use App\Authorization\ContextResource;
use App\Core\UserCache;
use App\Event\FlashTrait;
use App\Model\Entity\Attendance;
use App\Model\Entity\Division;
use App\Model\Entity\Game;
use App\Model\Entity\Person;
use App\Model\Entity\Setting;
use App\Model\Entity\Team;
use App\Model\Entity\TeamEvent;
use App\Model\Entity\User;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Cake\Http\Client;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class Listener implements EventListenerInterface {

	use FlashTrait;

	private $base_url = 'https://appjavelin.com/zuluru/';

	public function implementedEvents() {
		return [
			// Listeners to deal with overall plugin management
			'Javelin.register' => 'register',

			// Listeners to deal with roster updates
			'Model.Team.rosterUpdate' => 'rosterUpdate',
			'Model.Team.rosterRemove' => 'rosterRemove',
			'Model.Team.rosterDelete' => 'rosterDelete',

			// Listeners for general events that we may be interested in
			'Model.afterSave' => 'afterSave',
			'Model.afterDelete' => 'afterDelete',
			'Model.afterSaveCommit' => 'afterSaveCommit',
			'Model.afterDeleteCommit' => 'afterDeleteCommit',
			'Model.afterSaveRollback' => 'afterRollback',
			'Model.afterDeleteRollback' => 'afterRollback',

			// Listeners for events that collect elements to be displayed
			'Plugin.actions.team.links' => 'team_action_links',
			'Plugin.preferences' => 'preferences',
		];
	}

	public function register(Event $event, Person $contact) {
		$sports = TableRegistry::getTableLocator()->get('Leagues')
			->find()
			->distinct('sport')
			->extract('sport')
			->toArray();

		$data = [
			'site_url' => Router::url('/', true),
			'name' => Configure::read('organization.name'),
			'address' => Configure::read('organization.address'),
			'address2' => Configure::read('organization.address2'),
			'city' => Configure::read('organization.city'),
			'province' => Configure::read('organization.province'),
			'country' => Configure::read('organization.country'),
			'postal_code' => Configure::read('organization.postal'),
			'latitude' => Configure::read('organization.latitude'),
			'longitude' => Configure::read('organization.longitude'),
			'phone' => Configure::read('organization.phone'),
			'contact_first_name' => $contact->first_name,
			'contact_last_name' => $contact->last_name,
			'contact_email' => $contact->email,
			'admin_email' => Configure::read('email.admin_email'),
			'admin_name' => Configure::read('email.admin_name'),
			'support_email' => Configure::read('email.support_email'),
			'sports' => $sports,
		];

		$http = new Client();
		$response = $http->post($this->base_url . 'apikey.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}

		$body = json_decode($response->getStringBody(), true);
		$key = $body['api_key'];
		Configure::write('javelin.api_key', $key);

		$settings = TableRegistry::getTableLocator()->get('Settings');
		$setting = $settings->newEntity([
			'category' => 'javelin',
			'name' => 'api_key',
			'value' => $key,
		]);
		$settings->save($setting);
		Cache::delete('config', 'long_term');

		return true;
	}

	public function rosterUpdate(Event $event, $team, $people = []) {
		if (is_numeric($team)) {
			$team = TableRegistry::getTableLocator()->get('Teams')->get($team, [
				'contain' => [
					'Divisions',
				]
			]);
		}

		if (!$team->use_javelin) {
			return true;
		}

		$roster = TableRegistry::getTableLocator()->get('TeamsPeople')->find()
			->contain([
				'People' => [
					'Settings' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Settings.name' => 'javelin']);
						}
					],
				],
			])
			->where([
				'TeamsPeople.team_id' => $team->id,
				'TeamsPeople.status' => ROSTER_APPROVED,
				'TeamsPeople.role IN' => Configure::read('regular_roster_roles'),
			]);

		if (!empty($people)) {
			$roster->andWhere(['TeamsPeople.person_id IN' => collection($people)->extract('id')->toArray()]);
		}

		$coach_email = $coach_first = $coach_last = $coach_id = [];
		$player_email = $player_first = $player_last = $player_id = [];
		$default = Configure::read('javelin.default_opt_in', false);
		foreach ($roster as $player) {
			if (!empty($player->person->settings)) {
				$opt_in = $player->person->settings[0]->value;
			} else {
				$opt_in = $default;
			}

			if ($opt_in && !empty($player->person->email)) {
				if (in_array($player->role, Configure::read('privileged_roster_roles'))) {
					$coach_email[] = $player->person->email;
					$coach_first[] = $player->person->first_name;
					$coach_last[] = $player->person->last_name;
					$coach_id[] = $player->person->id;
				} else {
					$player_email[] = $player->person->email;
					$player_first[] = $player->person->first_name;
					$player_last[] = $player->person->last_name;
					$player_id[] = $player->person->id;
				}
			}
		}

		if (empty($coach_email) && empty($player_email)) {
			return true;
		}

		// This should only ever be populated when we're adding a new team via the Javelin teams controller
		// Apart from that, the coordinator data will already be in Javelin, and we don't need to send it.
		$coordinator_email = $coordinator_name = $coordinator_id = [];
		if (!empty($team->division->people)) {
			foreach ($team->division->people as $person) {
				if (!empty($person->settings)) {
					$opt_in = $person->settings[0]->value;
				} else {
					$opt_in = $default;
				}

				if ($opt_in) {
					$coordinator_email[] = $person->email;
					$coordinator_name[] = $person->full_name;
					$coordinator_id[] = $person->id;
				}
			}

			if (empty($coordinator_id)) {
				$coordinator_email[] = Configure::read('email.admin_email');
				$coordinator_name[] = Configure::read('email.admin_name');
			}
		}

		$data = array_merge($this->divisionData($team->division), [
			'team_id' => [$team->id],
			'coach_email' => [$coach_email],
			'coach_id' => [$coach_id],
			'coach_first' => [$coach_first],
			'coach_last' => [$coach_last],
			'player_email' => [$player_email],
			'player_id' => [$player_id],
			'player_first' => [$player_first],
			'player_last' => [$player_last],
			'coordinator_email' => $coordinator_email,
			'coordinator_name' => $coordinator_name,
		]);

		if (!empty($coordinator_id)) {
			$data['coordinator_id'] = $coordinator_id;
		}

		$http = new Client();
		$response = $http->post($this->base_url . 'team_league.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function rosterRemove(Event $event, $team, $person) {
		if (is_numeric($team)) {
			$team = TableRegistry::get('Teams')->get($team, [
				'contain' => [
					'Divisions',
				]
			]);
		}

		if (!$team->use_javelin) {
			return true;
		}

		$opt_in = TableRegistry::getTableLocator()->get('Settings')->find()
			->where(['Settings.name' => 'javelin', 'Settings.person_id' => $person->id])
			->first();
		if ($opt_in) {
			$opt_in = $opt_in->value;
		} else {
			$opt_in = Configure::read('javelin.default_opt_in', false);
		}

		if (!$opt_in) {
			return true;
		}

		$data = array_merge($this->divisionData($team->division), [
			'team_id' => $team->id,
			'player_id' => $person->id,
			'email' => $person->email,
			'type' => in_array($person->_joinData->role, Configure::read('privileged_roster_roles')) ? 'coach' : 'player',
		]);

		$http = new Client();
		$response = $http->post($this->base_url . 'removeroster.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function rosterDelete(Event $event, $team) {
		if (is_numeric($team)) {
			$team = TableRegistry::get('Teams')->get($team, [
				'contain' => [
					'Divisions',
				]
			]);
		}

		if (!$team->use_javelin) {
			return true;
		}

		$data = array_merge($this->divisionData($team->division), [
			'team_id' => $team->id,
		]);

		$http = new Client();
		$response = $http->post($this->base_url . 'deleteteam.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function afterSave(Event $event, EntityInterface $entity) {
		switch (get_class($entity)) {
			case 'App\Model\Entity\Attendance':
				return $this->afterSaveAttendance($event, $entity);

			case 'App\Model\Entity\Game':
				return $this->afterSaveGame($event, $entity);

			case 'App\Model\Entity\Person':
				return $this->afterSavePerson($event, $entity);

			case 'App\Model\Entity\Setting':
				return $this->afterSaveSetting($event, $entity);

			case 'App\Model\Entity\Team':
				return $this->afterSaveTeam($event, $entity);

			case 'App\Model\Entity\TeamEvent':
				return $this->afterSaveEvent($event, $entity);

			case 'App\Model\Entity\User':
				return $this->afterSaveUser($event, $entity);
		}
	}

	public function afterSaveAttendance(Event $event, Attendance $attendance) {
		if ($attendance->game_id || $attendance->isDirty('game_id')) {
			$type = $field = 'game_id';
		} else if ($attendance->team_event_id || $attendance->isDirty('team_event_id')) {
			$type = 'event_id';
			$field = 'team_event_id';
		} else {
			return true;
		}

		if (!$attendance->isDirty($field) && !$attendance->isDirty('status')) {
			return true;
		}

		$updated_attendance = Configure::read('javelin.updated_attendance', ['teams' => [], 'updates' => []]);

		$new_id = $attendance->$field;
		if ($new_id) {
			$key = "{$type}:{$attendance->team_id}:{$new_id}";
			if (!array_key_exists($key, $updated_attendance['updates'])) {
				$updated_attendance['updates'][$key] = [];
			}
			$updated_attendance['teams'][$attendance->team_id] = true;
			$updated_attendance['updates'][$key][$attendance->person_id] = $attendance->status;
		}

		Configure::write('javelin.updated_attendance', $updated_attendance);
	}

	public function afterSaveGame(Event $event, Game $game) {
		// We are interested in two types of changes to games. They never happen at the same time.

		// First type is a change to the score
		if ($game->isFinalized() && $game->isDirty('home_score')) {
			if (TableRegistry::getTableLocator()->get('Teams')->find()
				->where(['Teams.id IN' => [$game->home_team_id, $game->away_team_id], 'Teams.use_javelin' => true])
				->count() == 0
			) {
				return true;
			}

			$data = [
				'site_url' => Router::url('/', true),
				'api_key' => Configure::read('javelin.api_key'),
				'event_id' => $game->id,
				'status' => $game->status,
				'team_id' => [$game->home_team_id, $game->away_team_id],
			];
			if (!in_array($game->status, Configure::read('unplayed_status'))) {
				$data['score'] = [$game->home_score, $game->away_score];
			}

			// These updates just happen one at a time, no need to batch them up
			$http = new Client();
			$response = $http->post($this->base_url . 'scores.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

			if (!$response->isOk()) {
				return false;
			}
			$body = json_decode($response->getStringBody(), true);
			return $body['message'] == 'Success';
		}

		// Second type is a change to the schedule
		if (!$game->published && !$game->isDirty('published')) {
			return true;
		}

		$updated_teams = Configure::read('javelin.updated_schedules', []);

		if (($game->isDirty('published') && $game->published) || $game->isDirty('home_team_id')) {
			if ($game->home_team_id) {
				$updated_teams[$game->home_team_id] = true;
			}
			if ($game->getOriginal('home_team_id')) {
				$updated_teams[$game->getOriginal('home_team_id')] = true;
			}
		}

		if (($game->isDirty('published') && $game->published) || $game->isDirty('away_team_id')) {
			if ($game->away_team_id) {
				$updated_teams[$game->away_team_id] = true;
			}
			if ($game->getOriginal('away_team_id')) {
				$updated_teams[$game->getOriginal('away_team_id')] = true;
			}
		}

		Configure::write('javelin.updated_schedules', $updated_teams);

		if ($game->isDirty('published') && !$game->published) {
			// Javelin wants at least one team id, doesn't need both
			if ($game->home_team && $game->home_team->use_javelin) {
				$team_id = $game->home_team_id;
			} else if ($game->away_team && $game->away_team->use_javelin) {
				$team_id = $game->away_team_id;
			} else if ($game->home_team_id || $game->away_team_id) {
				$team = TableRegistry::getTableLocator()->get('Teams')->find()
					->where(['Teams.id IN' => [$game->home_team_id, $game->away_team_id], 'Teams.use_javelin' => true])
					->first();
				if (!$team) {
					return;
				}
				$team_id = $team->id;
			} else {
				return;
			}

			$deleted_games = Configure::read('javelin.deleted_games', []);
			$deleted_games[$game->id] = ['game_id' => $game->id, 'team_id' => $team_id];
			Configure::write('javelin.deleted_games', $deleted_games);
		}
	}

	public function afterSavePerson(Event $event, Person $person) {
		if (!$person->isDirty('first_name') && !$person->isDirty('last_name')) {
			// We're only interested in name updates
			return true;
		}

		TableRegistry::getTableLocator()->get('People')->loadInto($person, [
			Configure::read('Security.authModel'),
			'Settings' => [
				'queryBuilder' => function (Query $q) {
					return $q->where(['Settings.name' => 'javelin']);
				}
			]
		]);

		if (!empty($person->settings)) {
			$opt_in = $person->settings[0]->value;
		} else {
			$opt_in = Configure::read('javelin.default_opt_in', false);
		}

		if (!$opt_in) {
			// This is not a person who has opted in
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'type' => 'other',
			'email' => $person->email,
			'first_name' => $person->first_name,
			'last_name' => $person->last_name,
			'player_id' => $person->id,
		];
		$http = new Client();
		$response = $http->post($this->base_url . 'updateroster.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function afterSaveSetting(Event $event, Setting $setting) {
		// If it's an opt-in or opt-out of Javelin, we need to do roster "updates"
		if ($setting->category == 'personal' && $setting->name == 'javelin') {
			$teams = collection(UserCache::getInstance()->read('Teams', $setting->person_id))
				->match(['use_javelin' => true])
				->toArray();
			if ($setting->value) {
				if ($setting->person) {
					$person = $setting->person;
				} else {
					$person = TableRegistry::getTableLocator()->get('People')->get($setting->person_id);
				}
				foreach ($teams as $team) {
					$e = new Event('Model.Team.rosterUpdate', $this, [$team, [$person]]);
					EventManager::instance()->dispatch($e);
				}
			} else {
				foreach ($teams as $team) {
					$e = new Event('Model.Team.rosterRemove', $this, [$team, $setting->person_id]);
					EventManager::instance()->dispatch($e);
				}
			}
		}
	}

	public function afterSaveTeam(Event $event, Team $team) {
		if (!$team->isNew() && $team->isDirty('use_javelin')) {
			if (!$team->has('people')) {
				TableRegistry::getTableLocator()->get('Teams')->loadInto($team, ['People']);
			}
			$user_cache = UserCache::getInstance();
			foreach ($team->people as $person) {
				$user_cache->_deleteTeamData($person->id);
			}
		}
	}

	public function afterSaveEvent(Event $event, TeamEvent $team_event) {
		$events = Configure::read('javelin.updated_team_events', []);

		$events[] = [
			'event_id' => $team_event->id,
			'team_id' => $team_event->team_id,
			'name' => $team_event->name,
			'title' => (stripos($team_event->description, __('practice')) !== false) ? __('Practice') : $team_event->description,
			'event_type' => (stripos($team_event->description, __('practice')) !== false) ? 'practice' : 'other',
			'description' => $team_event->description,
			'address' => $team_event->location_name,
			'street' => $team_event->location_street,
			'city' => $team_event->location_city,
			'province' => $team_event->location_province,
			'start' => $team_event->start_time->toIso8601String(),
			'end' => $team_event->end_time->toIso8601String(),
			'website' => $team_event->website,
		];

		Configure::write('javelin.updated_team_events', $events);
	}

	public function afterSaveUser(Event $event, User $user) {
		$model_table = TableRegistry::getTableLocator()->get($user->getSource());
		$email_field = $model_table->emailField;
		if (!$user->isDirty($email_field)) {
			// We're only interested in email address updates
			return true;
		}

		$person = TableRegistry::getTableLocator()->get('People')->find()
			->contain([
				'Settings' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['Settings.name' => 'javelin']);
					}
				]
			])
			->where(['People.user_id' => $user->id])
			->first();

		if (!empty($person->settings)) {
			$opt_in = $person->settings[0]->value;
		} else {
			$opt_in = Configure::read('javelin.default_opt_in', false);
		}

		if (!$opt_in) {
			// This is not a person who has opted in
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'type' => 'email',
			'old_email' => $user->getOriginal($email_field),
			'new_email' => $user->$email_field,
			'player_id' => $person->id,
		];
		$http = new Client();
		$response = $http->post($this->base_url . 'updateroster.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function afterDelete(Event $event, EntityInterface $entity) {
		switch (get_class($entity)) {
			case 'App\Model\Entity\Game':
				return $this->afterDeleteGame($event, $entity);

			case 'App\Model\Entity\Team':
				return $this->rosterDelete($event, $entity);

			case 'App\Model\Entity\TeamEvent':
				return $this->afterDeleteEvent($event, $entity);
		}
	}

	public function afterDeleteGame(Event $event, Game $game) {
		if ($game->published) {
			$deleted_games = Configure::read('javelin.deleted_games', []);
			$deleted_games[$game->id] = true;
			Configure::write('javelin.deleted_games', $deleted_games);
		}
	}

	public function afterDeleteEvent(Event $event, TeamEvent $team_event) {
		$deleted_events = Configure::read('javelin.deleted_team_events', []);
		$deleted_events[$team_event->id] = [
			'event_id' => $team_event->id,
			'team_id' => $team_event->team_id,
		];
		Configure::write('javelin.deleted_team_events', $deleted_events);
	}

	public function afterSaveCommit(Event $event, $unused) {
		$this->sendUpdatedAttendance();
		$this->sendUpdatedSchedules();
		$this->sendDeletedGames();
		$this->sendUpdatedTeamEvents();
	}

	public function afterDeleteCommit(Event $event, $unused) {
		$this->sendDeletedGames();
		$this->sendDeletedTeamEvents();
	}

	private function sendUpdatedAttendance() {
		$updated_attendance = Configure::read('javelin.updated_attendance', []);
		if (empty($updated_attendance['teams'])) {
			return true;
		}

		Configure::delete('javelin.updated_attendance');
		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'attendance' => [],
		];

		$default = Configure::read('javelin.default_opt_in', false);
		$statuses = Configure::read('attendance');

		$teams = TableRegistry::getTableLocator()->get('Teams')->find('list')
			->where(['Teams.id IN' => array_keys($updated_attendance['teams']), 'Teams.use_javelin' => true])
			->toArray();
		foreach ($updated_attendance['updates'] as $key => $attendances) {
			[$type, $team, $game] = explode(':', $key);
			if (!array_key_exists($team, $teams)) {
				continue;
			}

			$game_data = [
				$type => $game,
				'team_id' => $team,
				'attendance' => [],
			];

			$people = TableRegistry::getTableLocator()->get('People')->find()
				->contain([
					Configure::read('Security.authModel'),
					'Settings' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Settings.name' => 'javelin']);
						}
					]
				])
				->where([
					'People.id IN' => array_keys($attendances),
				])
				->indexBy('id')
				->toArray();

			foreach ($attendances as $person_id => $status) {
				$person = $people[$person_id];

				$player_data = [
					'player_id' => $person_id,
					'name' => $person->full_name,
					'status' => $statuses[$status],
				];

				if (!empty($person->settings)) {
					$opt_in = $person->settings[0]->value;
				} else {
					$opt_in = $default;
				}
				if ($opt_in) {
					$player_data['email'] = $person->email;
				}

				$game_data['attendance'][] = $player_data;
			}

			$data['attendance'][] = $game_data;
		}

		if (empty($data['attendance'])) {
			return;
		}

		$http = new Client();
		$response = $http->post($this->base_url . 'attendance.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	private function sendUpdatedSchedules() {
		$updated_teams = Configure::read('javelin.updated_schedules');
		if (empty($updated_teams)) {
			return true;
		}

		Configure::delete('javelin.updated_schedules');
		$updated_teams = TableRegistry::get('Teams')->find()
			->select('Teams.id')
			->where([
				'id IN' => array_keys($updated_teams),
				'use_javelin' => true,
			])
			->extract('id')
			->toArray();
		if (empty($updated_teams)) {
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'team_id' => array_unique($updated_teams),
		];

		$http = new Client();
		$response = $http->post($this->base_url . 'team_event.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	private function sendDeletedGames() {
		$deleted_games = Configure::read('javelin.deleted_games');
		Configure::delete('javelin.deleted_games');
		if (empty($deleted_games)) {
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'games' => array_values($deleted_games),
		];

		$http = new Client();
		$response = $http->post($this->base_url . 'deleteevent.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	private function sendUpdatedTeamEvents() {
		$events = Configure::read('javelin.updated_team_events', []);
		Configure::delete('javelin.updated_team_events');
		if (empty($events)) {
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'events' => $events,
		];

		$http = new Client();
		$response = $http->post($this->base_url . 'otherevent.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	private function sendDeletedTeamEvents() {
		$deleted_events = Configure::read('javelin.deleted_team_events');
		Configure::delete('javelin.deleted_team_events');
		if (empty($deleted_events)) {
			return true;
		}

		$data = [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'events' => array_values($deleted_events),
		];

		$http = new Client();
		$response = $http->post($this->base_url . 'deleteevent.php', ['data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

		if (!$response->isOk()) {
			return false;
		}
		$body = json_decode($response->getStringBody(), true);
		return $body['message'] == 'Success';
	}

	public function afterRollback(Event $event, $unused) {
		Configure::delete('javelin.updated_attendance');
		Configure::delete('javelin.updated_schedules');
		Configure::delete('javelin.deleted_games');
		Configure::delete('javelin.updated_team_events');
	}

	public function team_action_links(Event $event, \ArrayObject $links, \ArrayObject $more, $authorize, $html, $team, $division) {
		if ($authorize->can('join', new ContextResource($team, ['division' => $division], 'Javelin'))) {
			$links[] = $html->iconLink('/javelin/img/javelin.png',
				['plugin' => 'javelin', 'controller' => 'Teams', 'action' => 'join', 'team' => $team->id],
				['alt' => __('Join  {0}', 'Javelin'), 'title' => __('Join  {0}', 'Javelin')],
				['confirm' => __('I am aware that this will send details of my team to a third-party application, and that there may be an additional charge to be paid to {0}.', 'Javelin')]);
		} else if ($authorize->can('leave', new ContextResource($team, ['division' => $division], 'Javelin'))) {
			$more[__('Leave  {0}', 'Javelin')] = [
				'url' => ['plugin' => 'javelin', 'controller' => 'Teams', 'action' => 'leave', 'team' => $team->id],
				'confirm' => __('Are you sure? This will delete all of your team\'s history from {0}.', 'Javelin'),
			];
		}
	}

	public function preferences(Event $event, \ArrayObject $elements) {
		$elements[] = 'Javelin.preferences';
	}

	private function divisionData(Division $division) {
		return [
			'site_url' => Router::url('/', true),
			'api_key' => Configure::read('javelin.api_key'),
			'league_id' => $division->league_id,
			'division_id' => $division->id,
		];
	}
}
