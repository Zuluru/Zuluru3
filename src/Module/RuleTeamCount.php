<?php
/**
 * Rule helper for returning how many teams a user is on.
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;

class RuleTeamCount extends Rule implements RuleHaving {

	/**
	 * Date range to look at
	 *
	 * @var FrozenDate[]
	 */
	protected $range;

	/**
	 * List of roles to look for
	 *
	 * @var string[]
	 */
	protected $roles;

	public function parse($config) {
		$config = array_map(function($item) {
			return trim($item, ' \'"');
		}, explode(',', $config));

		$sub_key = array_search('include_subs', $config);
		if ($sub_key !== false) {
			$this->roles = Configure::read('extended_playing_roster_roles');
			unset($config[$sub_key]);
		} else {
			$this->roles = Configure::read('playing_roster_roles');
		}

		$config = implode(',', $config);

		if ($config[0] == '<') {
			$to = substr($config, 1);
			try {
				$to = (new FrozenDate($to))->subDay();
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $to);
				return false;
			}
			$this->range = [new FrozenDate('0000-00-00'), $to];
		} else if ($config[0] == '>') {
			$from = substr($config, 1);
			try {
				$from = (new FrozenDate($from))->addDay();
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $from);
				return false;
			}
			$this->range = [$from, new FrozenDate('9999-12-31')];
		} else if (strpos($config, ',') !== false) {
			list($from, $to) = explode(',', $config);
			try {
				$from = new FrozenDate($from);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $from);
				return false;
			}
			try {
				$to = new FrozenDate($to);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $to);
				return false;
			}
			if ($from > $to) {
				$this->parse_error = __('The "from" date cannot be greater than the "to" date.');
				return false;
			}
			$this->range = [$from, $to];
		} else {
			try {
				$date = new FrozenDate($config);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $config);
				return false;
			}
			$this->range = [$date, $date];
		}

		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if (empty($params->teams)) {
			return 0;
		}
		return count(collection($params->teams)->filter(function ($team) use ($affiliate) {
			if ($team->has('_matchingData')) {
				$roster = $team->_matchingData['TeamsPeople'];
			} else if ($team->has('_joinData')) {
				$roster = $team->_joinData;
			} else {
				trigger_error('TODOTESTING', E_USER_WARNING);
				exit;
			}
			return in_array($roster->role, $this->roles) &&
				$roster->status == ROSTER_APPROVED &&
				$team->division->league->affiliate_id == $affiliate &&
				$team->division->open <= $this->range[1] &&
				$this->range[0] <= $team->division->close;
		})->toList());
	}

	protected function buildQuery(Query $query, $affiliate) {
		$query->innerJoin(['TeamsPeople' => 'teams_people'], 'TeamsPeople.person_id = People.id')
			->leftJoin(['Teams' => 'teams'], 'Teams.id = TeamsPeople.team_id')
			->leftJoin(['Divisions' => 'divisions'], 'Divisions.id = Teams.division_id')
			->group('People.id')
			->select(['team_count' => 'COUNT(Teams.id)'])
			->where([
				'Divisions.open <=' => $this->range[1],
				'Divisions.close >=' => $this->range[0],
				'TeamsPeople.role IN' => $this->roles,
				'TeamsPeople.status' => ROSTER_APPROVED,
			]);

		if (Configure::read('feature.affiliates')) {
			$query
				->leftJoin(['Leagues' => 'leagues'], 'Leagues.id = Divisions.league_id')
				->where(['Leagues.affiliate_id IN' => $affiliate]);
		}

		return true;
	}

	public function desc() {
		return __('have a team count');
	}

	public function having() {
		return 'team_count';
	}

}
