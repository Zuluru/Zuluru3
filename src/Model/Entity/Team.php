<?php
namespace App\Model\Entity;

use App\Core\UserCache;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Model\Results\TeamResults;

/**
 * Team Entity.
 *
 * @property int $id
 * @property string $name
 * @property int $division_id
 * @property string $website
 * @property string $shirt_colour
 * @property int $home_field_id
 * @property int $region_preference_id
 * @property bool $open_roster
 * @property int $rating
 * @property bool $track_attendance
 * @property int $attendance_reminder
 * @property int $attendance_summary
 * @property int $attendance_notification
 * @property int $initial_rating
 * @property int $affiliate_id
 * @property int $initial_seed
 * @property int $seed
 * @property string $flickr_user
 * @property string $flickr_set
 * @property bool $flickr_ban
 * @property string $logo
 * @property string $short_name
 * @property string $twitter_user
 *
 * @property \App\Model\Entity\Division $division
 * @property \App\Model\Entity\Field $field
 * @property \App\Model\Entity\Region $region
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Attendance[] $attendances
 * @property \App\Model\Entity\Incident[] $incidents
 * @property \App\Model\Entity\Note[] $notes
 * @property \App\Model\Entity\ScoreEntry[] $score_entries
 * @property \App\Model\Entity\SpiritEntry[] $spirit_entries
 * @property \App\Model\Entity\Stat[] $stats
 * @property \App\Model\Entity\TeamEvent[] $team_events
 * @property \App\Model\Entity\Franchise[] $franchises
 * @property \App\Model\Entity\Facility[] $facilities
 * @property \App\Model\Entity\Person[] $people
 *
 * @property \App\Model\Entity\Person[] $roster
 * @property \App\Model\Entity\Person[] $full_roster
 * @property \App\Model\Entity\Team $affiliated_team
 * @property bool $display_gender
 */
class Team extends Entity {

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

	/**
	 * @return array of approved non-sub players on the team
	 */
	protected function _getRoster() {
		return TableRegistry::get('People')->find()
			->matching('TeamsPeople', function (Query $q) {
				return $q->where([
					'TeamsPeople.team_id' => $this->id,
					'TeamsPeople.status' => ROSTER_APPROVED,
					'TeamsPeople.role IN' => Configure::read('regular_roster_roles'),
				]);
			});
	}

	/**
	 * @return array of all players on the team, regardless of status or role
	 */
	protected function _getFullRoster() {
		return TableRegistry::get('People')->find()
			->matching('TeamsPeople', function (Query $q) {
				return $q->where([
					'TeamsPeople.team_id' => $this->id,
				]);
			})
			->toArray();
	}

	/**
	 * @param $division Division Optional division record for the team being looked up
	 * @param $contain array Optional associations to contain on the read record
	 * @return Entity with the affiliated team record, or null if not found
	 */
	public function _getAffiliatedTeam($division = null, $contain = []) {
		if (empty($division)) {
			// Read the related division record
			$division = $this->division;
		}
		$season_divisions = $division->season_divisions;
		if (empty($season_divisions)) {
			return null;
		}

		$teams_table = TableRegistry::get('Teams');

		$franchises = $teams_table->Franchises->find()
			->select('id')
			->matching('Teams', function (Query $q) {
				return $q->where([
					'Teams.id' => $this->id,
				]);
			});
		$affiliated_teams = $teams_table->find()
			->contain($contain)
			->matching('Franchises', function (Query $q) use ($franchises) {
				return $q->where([
					'Franchises.id IN' => $franchises,
				]);
			})
			->where(['Teams.division_id IN' => $season_divisions]);
		if ($affiliated_teams->count() != 1) {
			return null;
		}
		return $affiliated_teams->first();
	}

	/**
	 * @return boolean indication of whether or not to show the gender field
	 */
	public function _getDisplayGender($person_id = null) {
		// If the team isn't in a division that's currently open, or opening soon, don't show it.
		if (!$this->division_id) {
			return false;
		}
		if ($this->has('division')) {
			$division = $this->division;
		} else {
			$division = TableRegistry::get('Divisions')->get($this->division_id);
		}
		if (!$division->is_open && !$division->open->isFuture()) {
			return false;
		}

		$on_team = in_array($this->id, UserCache::getInstance()->read('TeamIDs', $person_id));
		$admin = Configure::read('Perm.is_admin');
		$coordinator = in_array($this->division_id, UserCache::getInstance()->read('DivisionIDs', $person_id));
		$manager = Configure::read('Perm.is_manager') && in_array(TableRegistry::get('Divisions')->affiliate($this->division_id), UserCache::getInstance()->read('ManagedAffiliateIDs', $person_id));

		return $on_team || $admin || $coordinator || $manager;
	}

	public function consolidateRoster($sport) {
		if ($this->has('people')) {
			$this->roster_count = $this->skill_count = $this->skill_total = 0;
			foreach ($this->people as $person) {
				if (in_array($person->_joinData->role, Configure::read('playing_roster_roles')) &&
					$person->_joinData->status == ROSTER_APPROVED)
				{
					++$this->roster_count;
					if ($person->skills) {
						$skill = collection($person->skills)->firstMatch(['enabled' => true, 'sport' => $sport]);
					} else {
						$skill = TableRegistry::get('Skills')->find()
							->where(['person_id' => $person->id, 'enabled' => true, 'sport' => $sport])
							->first();
					}
					if (!empty($skill)) {
						++$this->skill_count;
						$this->skill_total += $skill->skill_level;
					}
				}
			}
			if ($this->skill_count) {
				$this->average_skill = sprintf('%.2f', round ($this->skill_total / $this->skill_count, 2));
			} else {
				$this->average_skill = 'N/A';
			}
		} else {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
	}

	public function twitterName() {
		static $handles = [];

		if (!empty($this->short_name)) {
			$ret = $this->short_name;
		} else {
			$ret = $this->name;
		}
		if (!empty($this->twitter_user) && !in_array($this->twitter_user, $handles)) {
			$ret .= " @{$this->twitter_user}";
			$handles[] = $this->twitter_user;
		}
		return $ret;
	}

	public function addGameResult($game, $league, $spirit_obj, $sport_obj) {
		if (!$this->has('_results')) {
			$this->_results = new TeamResults();
			$this->dirty('_results', false);
		}
		$this->_results->addGame($game, $this, $league, $spirit_obj, $sport_obj);
	}

}
