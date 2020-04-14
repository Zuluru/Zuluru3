<?php

/**
 * Derived class for implementing functionality for team events.
 */
namespace App\Module;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\TableRegistry;
use App\Core\UserCache;
use App\Core\ModuleRegistry;
use App\Model\Entity\Event;
use App\Model\Entity\Question;
use App\Model\Entity\Registration;
use App\Model\Entity\Response;

class EventTypeTeam extends EventType {
	public function configurationFields() {
		return ['level_of_play', 'ask_status', 'ask_region', 'ask_attendance'];
	}

	public function configurationFieldsElement() {
		return 'team';
	}

	public function configurationFieldsRules(EntityInterface $entity) {
		$ret = parent::schedulingFieldsRules($entity);

		$rule = new ExistsIn(['division_id'], 'Divisions');
		if (!$rule($entity, ['errorField' => 'division_id'])) {
			$entity->errors('division_id', ['validDivision' => __('You must select a valid division.')]);
			$ret = false;
		}

		return $ret;
	}

	// ID numbers don't much matter, but they can't be duplicated between event types,
	// and they can't ever be changed, because they're in the database.
	public function registrationFields(Event $event, $user_id, $for_output = false) {
		$fields = [
			new Question([
				'type' => 'group_start',
				'question' => __('Team Details'),
			]),
			new Question([
				'id' => TEAM_NAME,
				'type' => 'text',
				'question' => __('Team Name'),
				'help' => __('The full name of your team.'),
				'required' => true,
			]),
		];
		if (Configure::read('feature.shirt_colour')) {
			$fields[] = new Question([
				'id' => SHIRT_COLOUR,
				'type' => 'text',
				'question' => __('Shirt Colour'),
				'help' => __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.'),
				'required' => true,
			]);
		}

		if ($for_output) {
			$fields[] = new Question([
				'id' => TEAM_ID_CREATED,
				'type' => 'text',
				'question' => __('Team ID'),
			]);
		}

		// These questions are only meaningful when we are creating team records
		if (!empty($event->division_id)) {
			$teams_model = TableRegistry::getTableLocator()->get('Teams');

			if (Configure::read('feature.franchises')) {
				$conditions = [];

				// Possibly narrow the list of possible franchises to those that are represented
				// in the configured divisions
				if ($event->division->is_playoff) {
					$team_ids = UserCache::getInstance()->read('AllTeamIDs', $user_id);
					if (!empty($team_ids)) {
						$teams = $teams_model->find()
							->contain('Franchises')
							->where([
								'Teams.id IN' => $team_ids,
								'Teams.division_id IN' => $event->division->season_divisions,
							])
							->toArray();
						$conditions['Franchises.id IN'] = collection($teams)->extract('franchises.{*}.id')->toArray();
					}
				}

				$franchises = $teams_model->Franchises->readByPlayerId($user_id, $conditions);
				$franchises = collection($franchises)->combine('id', 'name')->toArray();

				// Teams added to playoff divisions must be in pre-existing franchises
				if ($event->division->is_playoff) {
					$extra = '<span class="warning-message">' . __('This MUST be the same franchise that the regular-season team belongs to, or you will NOT be able to correctly set up your roster.') . '</span>';
				} else {
					$franchises[NEW_FRANCHISE] = __('Create a new franchise');
					$extra = __('You may also choose to start a new franchise.');
				}

				$fields[] = new Question([
					'id' => FRANCHISE_ID,
					'type' => 'select',
					'question' => __('Franchise'),
					'help' => __('Select an existing franchise to add this team to. {0} You can only add teams to franchises you own; if you don\'t own the franchise this team should be added to, have the owner give you ownership before registering this team.', $extra),
					'options' => $franchises,
					'required' => true,
				]);
			}

			if (Configure::read('feature.region_preference') && !empty($event->ask_region)) {
				$regions_table = TableRegistry::getTableLocator()->get('Regions');
				$regions = $regions_table->find('list')
					->where(['affiliate_id' => $event->affiliate_id])
					->toArray();
				$fields[] = new Question([
					'id' => REGION_PREFERENCE,
					'type' => 'select',
					'question' => __('Region Preference'),
					'help' => __('Area of city where you would prefer to play.'),
					'options' => $regions,
				]);
			}

			if (!empty($event->ask_status)) {
				$fields[] = new Question([
					'id' => OPEN_ROSTER,
					'type' => 'checkbox',
					'question' => __('Open Roster'),
					'help' => __('If the team roster is open, others can request to join; otherwise, only a coach or captain can add players.'),
				]);
			}

			if (!empty($event->ask_attendance)) {
				$fields[] = new Question([
					'id' => TRACK_ATTENDANCE,
					'type' => 'checkbox',
					'question' => __('Attendance Tracking'),
					'default' => true,
					'help' => __('Would you like to enable attendance tracking for this team?'),
				]);
			}
		}

		$fields[] = new Question(['type' => 'group_end']);

		return $fields;
	}

	public function validateResponse($value, $context, Question $question, Array $responses, Event $event, Registration $registration = null) {
		switch ($question->id) {
			case TEAM_NAME:
				// If we're creating team records in a division, make sure the name is unique in that entire league
				if (!empty($event->division_id)) {
					// Simulate the context data that would be passed to the validator
					$context = [
						'data' => [
							'division' => $event->division,
						],
					];
					if ($registration) {
						$context['data']['id'] = $this->extractAnswer($registration->responses, TEAM_ID_CREATED);
					}
					if (!\App\Validation\Zuluru::teamUnique($value, $context)) {
						return __('There is already a team by that name in this league.');
					}
				}
				return true;

			case SHIRT_COLOUR:
				// We will accept any shirt colour
				// TODO: Perhaps add a "strict" option where people must choose from a preset list
				return true;

			case FRANCHISE_ID:
				if (Configure::read('feature.franchises')) {
					// -1 means make a new one with the same name as the team
					if ($value == -1) {
						$name = $this->extractAnswer($responses, TEAM_NAME);
						$context['data']['affiliate_id'] = $event->affiliate_id;
						if ($registration) {
							$context['data']['franchise_id'] = $this->extractAnswer($registration->responses, FRANCHISE_ID_CREATED);
						}
						if ($context['newRecord'] && !\App\Validation\Zuluru::franchiseUnique($name, $context)) {
							return __('New franchises are created with the same name as the team, but there is already a franchise with this name. To add this team to that franchise, you must be the franchise owner, which may require that the current owner add you as an owner.');
						}
					} else {
						if (!\App\Validation\Zuluru::franchiseOwner($value)) {
							return __('That franchise does not belong to you.');
						}
					}
				}
				return true;

			// TODO: Add region and open roster validation, if necessary
			case REGION_PREFERENCE:
			case OPEN_ROSTER:
			case TRACK_ATTENDANCE:
				return true;
		}

		return parent::validateResponse($value, $context, $question, $responses, $event, $registration);
	}

	// TODO: A site or per-league configuration controlling whether team records
	// are created when registered or when paid
	public function beforePaid(Event $event, Registration $registration, $options) {
		if (!$this->createTeam($event, $registration)) {
			return false;
		}
		return parent::beforePaid($event, $registration, $options);
	}

	public function beforeUnpaid(Event $event, Registration $registration, $options) {
		if (!parent::beforeUnpaid($event, $registration, $options)) {
			return false;
		}
		return $this->deleteTeam($event, $registration);
	}

	private function createTeam(Event $event, Registration $registration) {
		if (empty($event->division_id)) {
			return true;
		}

		$team = array_merge(
			[
				'division_id' => $event->division_id,
				// Set the captain as the person that registered the team
				'people' => [
					[
						'id' => $registration->person_id,
						'_joinData' => [
							'role' => 'captain',
							'status' => ROSTER_APPROVED,
							'position' => 'unspecified',
						],
					],
				],
			],
			$this->extractAnswers($registration->responses, [
				'name' => TEAM_NAME,
				'shirt_colour' => SHIRT_COLOUR,
				'region_id' => REGION_PREFERENCE,
				'open_roster' => OPEN_ROSTER,
				'track_attendance' => TRACK_ATTENDANCE,
			]),
			ModuleRegistry::getInstance()->load("LeagueType:{$event->division->schedule_type}")->newTeam()
		);
		if (!empty($team['track_attendance'])) {
			// Add some default values, chosen based on averages found in the TUC database so far
			$team += [
				'attendance_reminder' => 3,
				'attendance_summary' => 1,
				'attendance_notification' => 1,
			];
		}

		if (Configure::read('feature.franchises')) {
			$franchise = $this->extractAnswer($registration->responses, FRANCHISE_ID);
			// We may need to create a new franchise record
			if ($franchise == NEW_FRANCHISE) {
				$team['franchises'] = [
					[
						'name' => $team['name'],
						// Set the franchise owner as the person that registered the team
						'people' => [
							[
								'id' => $registration->person_id,
							],
						],
					],
				];
			} else {
				$team['franchises'] = [
					'_ids' => [$franchise],
				];
			}
		}

		$teams_model = TableRegistry::getTableLocator()->get('Teams');
		$team = $teams_model->newEntity($team, ['associated' => ['People', 'Franchises', 'Franchises.People']]);
		// TODO: This is hackish, but without it it thinks the person record is dirty and tries to save it,
		// which fails because of missing Affiliate containment
		$team->people[0]->setDirty('_joinData', false);

		// TeamsPeopleTable::afterSave needs access to the person entity
		if (!$teams_model->save($team, ['person' => $team->people[0]])) {
			return false;
		}

		$registration->responses[] = new Response([
			'question_id' => TEAM_ID_CREATED,
			'answer_text' => $team->id,
		]);
		$registration->setDirty('responses', true);

		if (!empty($team->franchises) && $franchise == NEW_FRANCHISE) {
			$registration->responses[] = new Response([
				'question_id' => FRANCHISE_ID_CREATED,
				'answer_text' => $team->franchises[0]->id,
			]);
		}

		UserCache::getInstance()->_deleteTeamData($registration->person_id);
		UserCache::getInstance()->_deleteFranchiseData($registration->person_id);

		return true;
	}

	private function deleteTeam(Event $event, Registration $registration) {
		if (empty($event->division_id)) {
			return true;
		}

		$team_id = $this->extractAnswer($registration->responses, TEAM_ID_CREATED);
		if (!$team_id) {
			return true;
		}

		$teams_model = TableRegistry::getTableLocator()->get('Teams');
		$team = $teams_model->get($team_id, [
			'contain' => ['People']
		]);
		if (!$teams_model->delete($team, ['registration' => $registration, 'event_obj' => $this])) {
			return false;
		}

		foreach ($team->people as $person) {
			UserCache::getInstance()->_deleteTeamData($person->id);
		}

		$registration->responses = collection($registration->responses)->filter(function ($response) {
			return !in_array($response->question_id, [TEAM_ID_CREATED, FRANCHISE_ID_CREATED]);
		})->toArray();
		$registration->setDirty('responses', true);

		return true;
	}

	public function beforeReregister(Event $event, Registration $registration, $options) {
		$team_id = $this->extractAnswer($registration->responses, TEAM_ID_CREATED);
		if ($team_id) {
			$teams_model = TableRegistry::getTableLocator()->get('Teams');
			$team = $teams_model->get($team_id, [
				'contain' => ['People']
			]);
			$team = $teams_model->patchEntity($team, $this->extractAnswers($registration->responses, [
				'name' => TEAM_NAME,
				'shirt_colour' => SHIRT_COLOUR,
				'region_id' => REGION_PREFERENCE,
				'open_roster' => OPEN_ROSTER,
				'track_attendance' => TRACK_ATTENDANCE,
			]));

			if ($team->isDirty()) {
				if (!$teams_model->save($team)) {
					return false;
				}
				foreach ($team->people as $person) {
					UserCache::getInstance()->_deleteTeamData($person->id);
				}
			}

			if (Configure::read('feature.franchises')) {
				$franchise_id = $this->extractAnswer($registration->responses, FRANCHISE_ID_CREATED);
				if ($franchise_id) {
					// Update the franchise name too
					$franchise = $teams_model->Franchises->get($franchise_id, [
						'contain' => ['People']
					]);
					$franchise = $teams_model->Franchises->patchEntity($franchise, $this->extractAnswers($registration->responses, [
						'name' => TEAM_NAME,
					]));

					if ($franchise->isDirty()) {
						if (!$teams_model->Franchises->save($franchise)) {
							return false;
						}
						foreach ($franchise->people as $person) {
							UserCache::getInstance()->_deleteFranchiseData($person->id);
						}
					}
				}
			}
		}

		return parent::beforeReregister($event, $registration, $options);
	}

	public function longDescription(Registration $registration) {
		$team = $this->extractAnswer($registration->responses, TEAM_NAME);
		return parent::longDescription($registration) . ": $team";
	}

}
