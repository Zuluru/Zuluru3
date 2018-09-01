<?php
/**
 * Rule helper for returning how many teams a user is on in the specified leagues.
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\ORM\Query;

class RuleLeagueTeamCount extends Rule implements RuleHaving {

	/**
	 * Leagues to look at
	 *
	 * @var int[]
	 */
	protected $leagues;

	/**
	 * List of roles to look for
	 *
	 * @var string[]
	 */
	protected $roles;

	public function parse($config) {
		$config = str_replace(['"', "'"], '', strtolower($config));
		$this->leagues = array_map('trim', explode(',', $config));
		$sub_key = array_search('include_subs', $this->leagues);
		if ($sub_key !== false) {
			$this->roles = Configure::read('extended_playing_roster_roles');
			unset($this->leagues[$sub_key]);
		} else {
			$this->roles = Configure::read('playing_roster_roles');
		}
		return true;
	}

	// Count how many teams the user was on in the given leagues.
	// If we're only interested in non-subs, if the user in
	// question is a sub on the current team, we'll just return 0.
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		$role = collection($team->people)->firstMatch(['id' => $params->id]);
		if ($role && !in_array($role->_joinData->role, $this->roles)) {
			return 0;
		}

		return count(collection($params->teams)->filter(function ($team) {
			return in_array($team->_matchingData['TeamsPeople']->role, $this->roles) &&
				$team->_matchingData['TeamsPeople']->status == ROSTER_APPROVED &&
				in_array($team->division->league->id, $this->leagues);
		})->toList());
	}

	protected function buildQuery(Query $query, $affiliate) {
		$query->leftJoin(['TeamsPeople' => 'teams_people'], 'TeamsPeople.person_id = People.id')
			->leftJoin(['Teams' => 'teams'], 'Teams.id = TeamsPeople.team_id')
			->leftJoin(['Divisions' => 'divisions'], 'Divisions.id = Teams.division_id')
			->group('People.id')
			->select(['team_count' => 'COUNT(Teams.id)'])
			->where([
				'Divisions.league_id IN' => $this->leagues,
				'TeamsPeople.role IN' => $this->roles,
				'TeamsPeople.status' => ROSTER_APPROVED,
			]);

		return true;
	}

	public function desc() {
		return __('have a team count');
	}

	public function having() {
		return 'team_count';
	}

}
