<?php
namespace App\Shell\Task;

use App\Middleware\ConfigurationLoader;
use App\PasswordHasher\HasherTrait;
use App\Controller\AppController;
use App\Model\Entity\Division;
use App\Model\Entity\Person;
use App\Model\Entity\Team;
use App\Model\Entity\TeamsPerson;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * RosterEmails Task
 *
 * @property \App\Model\Table\ActivityLogsTable $logs_table
 */
class RosterEmailsTask extends Shell {

	use HasherTrait;

	public function main() {
		ConfigurationLoader::loadConfiguration();
		if (!Configure::read('feature.generate_roster_email')) {
			return;
		}

		$rosters = TableRegistry::get('TeamsPeople')->find()
			->contain([
				'People' => [
					Configure::read('Security.authModel'),
				],
			])
			->where([
				'TeamsPeople.status IN' => [ROSTER_INVITED, ROSTER_REQUESTED],
				'TeamsPeople.created <' => FrozenDate::now()->subDays(7),
			])
			->all();
		if ($rosters->isEmpty()) {
			return;
		}

		// Read all required team records
		$teams = TableRegistry::get('Teams')->find()
			->contain([
				'Divisions' => [
					'Days',
					'Leagues',
				],
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q
							->where([
								'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
								'TeamsPeople.status' => ROSTER_APPROVED,
							])
							->order('TeamsPeople.id');
					},
					Configure::read('Security.authModel'),
				],
			])
			->where([
				'Teams.id IN' => array_unique(collection($rosters)->extract('team_id')->toList()),
			])
			->indexBy('id')
			->toArray();

		$this->logs_table = TableRegistry::get('ActivityLogs');

		foreach ($rosters as $roster) {
			$team_id = $roster->team_id;
			$conditions = [
				'type' => ($roster->status == ROSTER_INVITED ? 'roster_invite_reminder' : 'roster_request_reminder'),
				'team_id' => $team_id,
				'person_id' => $roster->person_id,
			];
			$sent = $this->logs_table->find()
				->where($conditions)
				->order('created');
			if (!$sent->isEmpty()) {
				$log = $sent->first();
				if ($log->created->addHours(7.5 * 24)->isPast()) {
					// Expire invites that have had reminders sent more than 7.5 days ago
					$this->_rosterExpire($roster->person, $teams[$team_id]->people, $teams[$team_id], $teams[$team_id]->division, $roster);
				} else if ($log->created->addHours(5.5 * 24)->isPast() && $sent->count() < 2) {
					// Second reminder for people that have had reminders sent more than 5.5 days ago
					$this->_rosterRemind($roster->person, $teams[$team_id]->people, $teams[$team_id], $teams[$team_id]->division, $roster, true);
				}
			} else {
				$this->_rosterRemind($roster->person, $teams[$team_id]->people, $teams[$team_id], $teams[$team_id]->division, $roster);
			}
		}
	}

	protected function _rosterRemind(Person $person, $captains, Team $team, Division $division = null, TeamsPerson $roster, $second = false) {
		$code = $this->_makeHash([$roster->id, $roster->team_id, $roster->person_id, $roster->role, $roster->created]);
		if (!empty($division)) {
			$league = $division->league;
			$sport = $league->sport;
		} else {
			$sport = current(array_keys(Configure::read('options.sport')));
		}

		$viewVars = array_merge(compact('person', 'team', 'division', 'league', 'roster', 'code', 'sport'), [
			'captains' => implode(', ', collection($captains)->extract('first_name')->toList()),
			'days' => ($second ? 2 : 7),
		]);

		if ($roster->status == ROSTER_INVITED) {
			if (!AppController::_sendMail([
				'to' => $person,
				'replyTo' => $captains,
				'subject' => __('Reminder of invitation to join {0}', $team->name),
				'template' => 'roster_invite_reminder',
				'sendAs' => 'both',
				'ignore_empty_address' => true,
				'viewVars' => $viewVars,
				'header' => [
					'Auto-Submitted' => 'auto-generated',
					'X-Auto-Response-Suppress' => 'OOF',
				],
			]))
			{
				return false;
			}

			// If this is the second reminder, we also tell the captain(s)
			if ($second && !empty($captains)) {
				if (!AppController::_sendMail([
					'to' => $captains,
					'replyTo' => $person,
					'subject' => __('{0} has not answered invitation to join {1}', $person->full_name, $team->name),
					'template' => 'roster_invite_captain_reminder',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
					'viewVars' => $viewVars,
					'header' => [
						'Auto-Submitted' => 'auto-generated',
						'X-Auto-Response-Suppress' => 'OOF',
					],
				]))
				{
					return false;
				}
			}
		} else {
			if (!empty($captains) && !AppController::_sendMail([
					'to' => $captains,
					'replyTo' => $person,
					'subject' => __('Reminder of {0} request to join {1}', $person->full_name, $team->name),
					'template' => 'roster_request_reminder',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
					'viewVars' => $viewVars,
					'header' => [
						'Auto-Submitted' => 'auto-generated',
						'X-Auto-Response-Suppress' => 'OOF',
					],
				]))
			{
				return false;
			}

			// If this is the second reminder, we also tell the player
			if ($second) {
				if (!AppController::_sendMail([
					'to' => $person,
					'replyTo' => $captains,
					'subject' => __('Unanswered request to join {0}', $team->name),
					'template' => 'roster_request_player_reminder',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
					'viewVars' => $viewVars,
					'header' => [
						'Auto-Submitted' => 'auto-generated',
						'X-Auto-Response-Suppress' => 'OOF',
					],
				]))
				{
					return false;
				}
			}
		}

		$this->logs_table->save($this->logs_table->newEntity([
			'type' => ($roster->status == ROSTER_INVITED ? 'roster_invite_reminder' : 'roster_request_reminder'),
			'team_id' => $roster->team_id,
			'person_id' => $roster->person_id,
		]));

		return true;
	}

	protected function _rosterExpire($person, $captains, $team, $division, $roster) {
		$viewVars = array_merge(compact('person', 'team', 'division', 'roster'), [
			'captains' => implode(', ', collection($captains)->extract('first_name')->toList()),
		]);

		if ($roster['status'] == ROSTER_INVITED) {
			if (!AppController::_sendMail([
				'to' => $captains,
				'cc' => $person,
				'replyTo' => $person,
				'subject' => __('{0} invitation to join {1} expired', $person->full_name, $team->name),
				'template' => 'roster_invite_expire',
				'sendAs' => 'both',
				'ignore_empty_address' => true,
				'viewVars' => $viewVars,
				'header' => [
					'Auto-Submitted' => 'auto-generated',
					'X-Auto-Response-Suppress' => 'OOF',
				],
			]))
			{
				return false;
			}
		} else {
			if (!AppController::_sendMail([
				'to' => $person,
				'cc' => $captains,
				'replyTo' => $captains,
				'subject' => __('{0} request to join {1} expired', $person->full_name, $team->name),
				'template' => 'roster_request_expire',
				'sendAs' => 'both',
				'ignore_empty_address' => true,
				'viewVars' => $viewVars,
				'header' => [
					'Auto-Submitted' => 'auto-generated',
					'X-Auto-Response-Suppress' => 'OOF',
				],
			]))
			{
				return false;
			}
		}

		// Delete the invite/request
		if (!TableRegistry::get('TeamsPeople')->delete($roster)) {
			return false;
		}

		return true;
	}

}
