<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use App\Model\Results\DivisionResults;

/**
 * Division Entity.
 *
 * @property int $id
 * @property string $name
 * @property \Cake\I18n\FrozenTime $open
 * @property \Cake\I18n\FrozenTime $close
 * @property string $ratio
 * @property string $current_round
 * @property \Cake\I18n\FrozenTime $roster_deadline
 * @property string $roster_rule
 * @property bool $is_open
 * @property string $schedule_type
 * @property int $games_before_repeat
 * @property string $allstars
 * @property bool $exclude_teams
 * @property string $coord_list
 * @property string $capt_list
 * @property int $email_after
 * @property int $finalize_after
 * @property string $roster_method
 * @property int $league_id
 * @property string $rating_calculator
 * @property bool $flag_membership
 * @property bool $flag_roster_conflict
 * @property bool $flag_schedule_conflict
 * @property string $allstars_from
 * @property string $header
 * @property string $footer
 * @property bool $double_booking
 * @property string $most_spirited
 *
 * @property \App\Model\Entity\League $league
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Game[] $games
 * @property \App\Model\Entity\Pool[] $pools
 * @property \App\Model\Entity\Team[] $teams
 * @property \App\Model\Entity\Day[] $days
 * @property \App\Model\Entity\GameSlot[] $game_slots
 * @property \App\Model\Entity\Person[] $people
 *
 * @property string $league_name
 * @property string $long_league_name
 * @property string $full_league_name
 * @property int[] $playoff_divisions
 * @property int[] $season_divisions
 * @property int[] $season_days
 * @property int[] $sister_divisions
 * @property bool $is_playoff
 * @property bool $roster_deadline_passed
 * @property bool $women_present
 */
class Division extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'league_name', 'long_league_name', 'full_league_name',
		'is_playoff', 'sister_divisions', 'playoff_divisions', 'season_divisions', 'season_days'
	];

	//
	// Specifics of getting "full" division names, including league name, season, etc.
	//

	// Cache results of finding the league record
	private $_league_record = false;

	private function _getLeagueRecord() {
		if ($this->_league_record === false) {
			if (!isset($this->league)) {
				if ($this->league_id !== null) {
					$this->_league_record = TableRegistry::get('Leagues')->get($this->league_id);
				}
			} else {
				$this->_league_record = $this->league;
			}
		}

		return $this->_league_record;
	}

	protected function _getLeagueName() {
		$league = $this->_getLeagueRecord();
		if (!$league) {
			return null;
		}
		return trim($league->name . ' ' . $this->name);
	}

	protected function _getLongLeagueName() {
		$league = $this->_getLeagueRecord();
		if (!$league) {
			return null;
		}
		return trim($league->long_name . ' ' . $this->name);
	}

	protected function _getFullLeagueName() {
		$league = $this->_getLeagueRecord();
		if (!$league) {
			return null;
		}
		return trim($league->full_name . ' ' . $this->name);
	}

	//
	// Specifics of getting playoff-related information, for when not
	// all teams participate in playoffs.
	//

	// Cache results of finding related divisions
	private $_playoff_divisions = false;
	private $_season_divisions = false;

	protected function _getPlayoffDivisions() {
		if ($this->_playoff_divisions === false) {
			$this->_playoff_divisions = TableRegistry::get('Divisions')->find()
				->select('id')
				->where([
					'league_id' => $this->league_id,
					'current_round' => 'playoff',
				])
				->extract('id')
				->toArray();
		}

		if ($this->current_round == 'playoff') {
			return [];
		} else {
			return $this->_playoff_divisions;
		}
	}

	protected function _getSeasonDivisions() {
		if ($this->_season_divisions === false) {
			$this->_season_divisions = TableRegistry::get('Divisions')->find()
				->contain(['Days'])
				->where([
					'league_id' => $this->league_id,
					'current_round !=' => 'playoff',
				])
				->toArray();
		}

		if ($this->current_round == 'playoff') {
			return collection($this->_season_divisions)->extract('id')->toArray();
		} else {
			return [];
		}
	}

	protected function _getSeasonDays() {
		if ($this->current_round == 'playoff') {
			$this->_getSeasonDivisions();
			return array_unique(collection($this->_season_divisions)->extract('days.{*}.id')->toList());
		} else {
			return [];
		}
	}

	protected function _getSisterDivisions() {
		if ($this->current_round == 'playoff') {
			$this->_getPlayoffDivisions();
			return $this->_playoff_divisions;
		} else {
			$this->_getSeasonDivisions();
			return collection($this->_season_divisions)->extract('id')->toArray();
		}
	}

	protected function _getIsPlayoff() {
		if ($this->current_round == 'playoff') {
			$this->_getSeasonDivisions();
			return !empty($this->_season_divisions);
		} else {
			return false;
		}
	}

	// TODO: Change this to a more typical accessor
	public function rosterDeadline() {
		if ($this->roster_deadline === null) {
			return $this->close;
		}
		return $this->roster_deadline;
	}

	protected function _getRosterDeadlinePassed() {
		return $this->rosterDeadline()->isPast();
	}

	protected function _getWomenPresent() {
		return Configure::read('scoring.women_present') && Configure::read("sports.{$this->league->sport}.variable_gender_ratio.{$this->ratio_rule}");
	}

	public function addGameResult($game) {
		if (!$this->has('_results')) {
			$this->_results = new DivisionResults();
			$this->dirty('_results', false);
		}
		$this->_results->addGame($game);
	}

}
