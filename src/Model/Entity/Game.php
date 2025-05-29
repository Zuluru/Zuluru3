<?php
namespace App\Model\Entity;

use App\Core\ModuleRegistry;
use App\Model\Table\GamesTable;
use App\Module\Spirit;
use App\Service\Games\ScoreService;
use App\Service\Games\SpiritService;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;

/**
 * Game Entity.
 *
 * @property int $id
 * @property int $division_id
 * @property string $round
 * @property int $tournament_pool
 * @property string $name
 * @property int $placement
 * @property string $home_dependency_type
 * @property int $home_dependency_id
 * @property int $home_team_id
 * @property string $away_dependency_type
 * @property int $away_dependency_id
 * @property int $away_team_id
 * @property int $home_score
 * @property int $away_score
 * @property int $rating_points
 * @property int $approved_by_id
 * @property string $status
 * @property bool $published
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $type
 * @property int $pool_id
 * @property int $home_pool_team_id
 * @property int $away_pool_team_id
 * @property int $game_slot_id
 * @property int $home_field_rank
 * @property int $away_field_rank
 * @property int $home_carbon_flip
 *
 * @property \App\Model\Entity\Division $division
 * @property \App\Model\Entity\Team $home_team
 * @property \App\Model\Entity\Team $away_team
 * @property \App\Model\Entity\Person $approved_by
 * @property \App\Model\Entity\Pool $pool
 * @property \App\Model\Entity\PoolsTeam $home_pool_team
 * @property \App\Model\Entity\PoolsTeam $away_pool_team
 * @property \App\Model\Entity\GameSlot $game_slot
 * @property \App\Model\Entity\Attendance[] $attendances
 * @property \App\Model\Entity\Incident[] $incidents
 * @property \App\Model\Entity\ScoreDetail[] $score_details
 * @property \App\Model\Entity\ScoreEntry[] $score_entries
 * @property \App\Model\Entity\SpiritEntry[] $spirit_entries
 * @property \App\Model\Entity\ActivityLog[] $score_reminder_emails
 * @property \App\Model\Entity\ActivityLog[] $score_mismatch_emails
 * @property \App\Model\Entity\ActivityLog[] $attendance_reminder_emails
 * @property \App\Model\Entity\ActivityLog[] $attendance_summary_emails
 * @property \App\Model\Entity\Note[] $notes
 * @property \App\Model\Entity\Stat[] $stats
 * @property \App\Model\Entity\Person[] $officials
 * @property \App\Model\Entity\Team[] $team_officials
 *
 * @property string $display_name
 * @property string $home_dependency
 * @property string $away_dependency
 * @property \Cake\I18n\FrozenTime $start_time
 * @property \Cake\I18n\FrozenTime $end_time
 */
class Game extends Entity {

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

	// TODOLATER: Test this lazy loading more. If it's good, make it more generic, in AppModel.
	/*
	protected function _getHomeTeam($team) {
		if (empty($team) && $this->home_team_id) {
			$teams_table = TableRegistry::getTableLocator()->get('Teams');
			try {
				$team = $this->home_team = $teams_table->get($this->home_team_id);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				return null;
			}
		}

		return $team;
	}
	*/

	protected function _getDisplayName() {
		if ($this->placement) {
			return Number::ordinal($this->placement);
		} else {
			return $this->name;
		}
	}

	protected function _getStartTime() {
		if ($this->game_slot) {
			return $this->game_slot->start_time;
		}
		return new FrozenTime(0);
	}

	protected function _getEndTime() {
		if ($this->game_slot) {
			return $this->game_slot->end_time;
		}
		return new FrozenTime(0);
	}

	/**
	 * Take what is currently known about the game, and finalize it.
	 * If we have:
	 * 	0) no scores entered
	 * 		- forfeit game as 0-0 tie
	 * 		- give poor spirit to both
	 * 	1) one score entered
	 * 		- use single score as final
	 * 		- give full spirit to entering team, assigned spirit, less
	 * 		  some configurable penalty, to non-entering team.
	 * 	2) two scores entered, not agreeing
	 * 		- send email to the coordinator(s).
	 *  3) two scores entered, agreeing
	 *  	- scores are entered as provided, as are spirit values.
	 */
	public function finalize(?ScoreEntry $home_score_entry, ?ScoreEntry $away_score_entry) {
		// Initialize data to be saved
		$spirit_obj = $this->division->league->hasSpirit() ? ModuleRegistry::getInstance()->load("Spirit:{$this->division->league->sotg_questions}") : null;
		$spirit_service = new SpiritService($this->spirit_entries ?? [], $spirit_obj);
		$score_service = new ScoreService($this->score_entries ?? []);

		if ($home_score_entry && $home_score_entry->person_id && $away_score_entry && $away_score_entry->person_id) {
			if ($score_service->scoreEntriesAgree($home_score_entry, $away_score_entry)) {
				$this->status = $home_score_entry->status;

				if ($home_score_entry->status == 'normal') {
					// No default.  Just finalize score.
					$this->home_score = $home_score_entry->score_for;
					$this->away_score = $home_score_entry->score_against;
					$this->home_carbon_flip = $home_score_entry->home_carbon_flip;
				}

				$this->approved_by_id = APPROVAL_AUTOMATIC;
			} else {
				// Maybe send a notification email to the convener
				$event = new CakeEvent('Model.Game.scoreMismatch', $this, [$this]);
				EventManager::instance()->dispatch($event);
				return false;
			}
		} else if ($home_score_entry && $home_score_entry->person_id && (!$away_score_entry || !$away_score_entry->person_id)) {
			$this->status = $home_score_entry->status;

			switch ($home_score_entry->status) {
				case 'home_default':
				case 'away_default':
					// We don't need to do anything with the scores here. beforeSave will call
					// adjustScoreAndRatings, which will take care of that.
					break;

				case 'normal':
					$this->home_score = $home_score_entry->score_for;
					$this->away_score = $home_score_entry->score_against;
					$this->home_carbon_flip = $home_score_entry->home_carbon_flip;
					break;

				default:
					$this->home_score = $this->away_score = null;
					break;
			}

			if ($spirit_obj && !in_array($home_score_entry->status, Configure::read('unplayed_status'))) {
				$spirit_service->addAwayPenaltyEntry($this);
			}

			$this->approved_by_id = APPROVAL_AUTOMATIC_HOME;
			$event = new CakeEvent('Model.Game.scoreApproval', $this, [$this, $this->away_team, $this->home_team]);
			EventManager::instance()->dispatch($event);
		} else if ((!$home_score_entry || !$home_score_entry->person_id) && $away_score_entry && $away_score_entry->person_id) {
			$this->status = $away_score_entry->status;

			switch ($away_score_entry->status) {
				case 'home_default':
				case 'away_default':
					// We don't need to do anything with the scores here. beforeSave will call
					// adjustScoreAndRatings, which will take care of that.
					break;

				case 'normal':
					$this->home_score = $away_score_entry->score_against;
					$this->away_score = $away_score_entry->score_for;
					$this->home_carbon_flip = $away_score_entry->home_carbon_flip;
					break;

				default:
					$this->home_score = $this->away_score = null;
					break;
			}

			if ($spirit_obj && !in_array($away_score_entry->status, Configure::read('unplayed_status'))) {
				$spirit_service->addHomePenaltyEntry($this);
			}

			$this->approved_by_id = APPROVAL_AUTOMATIC_AWAY;
			$event = new CakeEvent('Model.Game.scoreApproval', $this, [$this, $this->home_team, $this->away_team]);
			EventManager::instance()->dispatch($event);
		} else {
			// TODO: don't do automatic forfeit yet.  Make it per-league configurable
			return __('No score entry found for either team; cannot finalize this game.');
		}

		if ($spirit_obj && !in_array($this->status, Configure::read('unplayed_status'))) {
			$this->spirit_entries = $spirit_service->getEntries();
		} else {
			$this->spirit_entries = [];
		}
		$this->setDirty('spirit_entries', true);

		return true;
	}

	public function updateDependencies() {
		// There should not ever be ties in dependency games, but in the rare case where there is,
		// we'll give the win to the home team, on the assumption that they're the home team for
		// a good reason.
		if ($this->home_score >= $this->away_score) {
			$winner = $this->home_team_id;
			$loser = $this->away_team_id;
		} else {
			$winner = $this->away_team_id;
			$loser = $this->home_team_id;
		}

		// Look for games with this as a game dependency
		$games_table = TableRegistry::getTableLocator()->get('Games');
		foreach (['home', 'away'] as $type) {
			$dependency_field = "{$type}_dependency_type";
			$team_field = "{$type}_team_id";

			$games = $games_table->find()
				->where([
					"{$type}_dependency_type LIKE" => 'game_%',
					"{$type}_dependency_id" => $this->id,
				]);
			foreach ($games as $dependency) {
				if ($dependency->$dependency_field == 'game_winner') {
					$dependency->$team_field = $winner;
				}
				if ($dependency->$dependency_field == 'game_loser') {
					$dependency->$team_field = $loser;
				}
				$games_table->save($dependency);
			}
		}
	}

	public function adjustScoreAndRatings() {
		if ($this->getOriginal('status') != $this->status) {
			switch ($this->status) {
				case 'home_default':
					$this->home_score = Configure::read('scoring.default_losing_score');
					$this->away_score = Configure::read('scoring.default_winning_score');
					break;

				case 'away_default':
					$this->home_score = Configure::read('scoring.default_winning_score');
					$this->away_score = Configure::read('scoring.default_losing_score');
					break;

				case 'normal':
					break;

				default:
					$this->home_score = $this->away_score = null;
					break;
			}
		}

		// Finalize the rating change if we've just updated the score
		// TODO: This probably needs to be changed to handle a situation where a game was finalized at some score and then changed to a default with the same score.
		if ($this->getOriginal('home_score') != $this->home_score || $this->getOriginal('away_score') != $this->away_score) {
			$this->modifyTeamRatings();

			// If this league has stat tracking, we may need to update some calculated stats
			if ($this->has('division') && $this->division->league->hasStats()) {
				if (($this->home_score < $this->away_score && $this->getOriginal('home_score') >= $this->getOriginal('away_score')) ||
					($this->home_score > $this->away_score && $this->getOriginal('home_score') <= $this->getOriginal('away_score')) ||
					($this->home_score == $this->away_score && $this->getOriginal('home_score') != $this->getOriginal('away_score')))
				{
					$calc_stats = TableRegistry::getTableLocator()->get('StatTypes')->find()
						->where([
							'StatTypes.type' => 'game_calc',
							'StatTypes.sport' => $this->division->league->sport,
						]);
					$sport_obj = ModuleRegistry::getInstance()->load("Sport:{$this->division->league->sport}");

					foreach ($calc_stats as $stat_type) {
						$func = "{$stat_type->handler}GameRecalculate";
						if (method_exists($sport_obj, $func)) {
							$sport_obj->$func($stat_type, $this);
						}
					}

					if ($this->home_team_id) {
						Cache::delete("team_{$this->home_team_id}_stats", 'long_term');
					}
					if ($this->away_team_id) {
						Cache::delete("team_{$this->away_team_id}_stats", 'long_term');
					}
					TableRegistry::getTableLocator()->get('Divisions')->clearCache($this->division, ['stats']);
				}
			}

			// Any time that this is called, the division seeding might change.
			// We just reset it here, and it will be recalculated as required elsewhere.
			TableRegistry::getTableLocator()->get('Teams')->updateAll(['seed' => 0], ['division_id' => $this->division_id]);
		}
	}

	/**
	 * If we already have a rating, reverse the effect of this game from the team ratings.
	 */
	public function undoRatings() {
		if (!empty($this->rating_points)) {
			if (!$this->has('home_team')) {
				TableRegistry::getTableLocator()->get('Games')->loadInto($this, ['HomeTeam', 'AwayTeam']);
			}
			if ($this->getOriginal('home_score') >= $this->getOriginal('away_score')) {
				$this->home_team->rating -= $this->rating_points;
				if ($this->away_team) {
					$this->away_team->rating += $this->rating_points;
				}
			} else {
				$this->home_team->rating += $this->rating_points;
				if ($this->away_team) {
					$this->away_team->rating -= $this->rating_points;
				}
			}
			$this->setDirty('home_team', true);
			$this->setDirty('away_team', true);
		}
	}

	/**
	 * Calculate the value to be added/subtracted from the competing
	 * teams' ratings, using the defined league module.
	 */
	public function modifyTeamRatings() {
		$this->undoRatings();

		// If we're not a normal game, avoid changing the rating.
		$change_rating = false;
		if ($this->status == 'normal') {
			$change_rating = true;
		}
		if (Configure::read('scoring.default_transfer_ratings') &&
			($this->status == 'home_default' || $this->status == 'away_default') )
		{
			$change_rating = true;
		}
		if ($this->type != SEASON_GAME) {
			$change_rating = false;
		}

		if (!$change_rating) {
			$this->rating_points = 0;
			return true;
		}

		$ratings_obj = ModuleRegistry::getInstance()->load("Ratings:{$this->division->rating_calculator}");

		// For a tie, we assume the home team wins
		if ($this->home_score >= $this->away_score) {
			$change = $ratings_obj->calculateRatingsChange($this->home_score, $this->away_score,
				$ratings_obj->calculateExpectedWin($this->home_team->rating, $this->away_team->rating));
			$this->home_team->rating += $change;
			if ($this->away_team) {
				$this->away_team->rating -= $change;
			}
		} else {
			$change = $ratings_obj->calculateRatingsChange($this->home_score, $this->away_score,
				$ratings_obj->calculateExpectedWin($this->away_team->rating, $this->home_team->rating));
			$this->home_team->rating -= $change;
			if ($this->away_team) {
				$this->away_team->rating += $change;
			}
		}

		$this->rating_points = $change;

		if ($change) {
			$this->setDirty('home_team', true);
			$this->setDirty('away_team', true);
		}

		return true;
	}

	/**
	 * Retrieve score reminder email record for given team.
	 *
	 * @param int $team_id ID of the team to find the score reminder for
	 * @return mixed Entity with the requested score reminder, or false if the team hasn't received a score reminder yet.
	 */
	public function getScoreReminderEmail($team_id) {
		if (!empty($this->score_reminder_emails)) {
			foreach ($this->score_reminder_emails as $entry) {
				if ($entry->team_id == $team_id) {
					return $entry;
				}
			}
		}

		try {
			$games_table = TableRegistry::getTableLocator()->get('Games');
			$entry = $games_table->ScoreReminderEmails->find()
				->where([
					'game_id' => $this->id,
					'team_id' => $team_id,
				])
				->firstOrFail();
		} catch (RecordNotFoundException $ex) {
			return false;
		}

		return $entry;
	}

	public function isFinalized() {
		// A game in progress is not finalized
		if ($this->status == 'in_progress') {
			return false;
		}
		// Any status other than in_progress or normal means it is finalized without having been played
		if ($this->status != 'normal') {
			return true;
		}
		// If there's a home score, it was played, and the score was approved
		if ($this->home_score !== null) {
			return true;
		}
		// Score has not yet been approved
		return false;
	}

	public function readDependencies() {
		if (!empty($this->home_dependency_type)) {
			$this->readDependency($this->home_pool_team, 'home');
		}

		if (!empty($this->away_dependency_type)) {
			$this->readDependency($this->away_pool_team, 'away');
		}
	}

	private function readDependency($pool, $type) {
		$games_table = TableRegistry::getTableLocator()->get('Games');
		$type .= '_dependency';
		$id_prop = "{$type}_id";
		$type_prop = "{$type}_type";
		$id = $this->$id_prop;
		switch ($this->$type_prop) {
			case 'game_winner':
				$game = $games_table->field('name', ['Games.id' => $id]);
				$dependency = __('Winner of game {0}', $game);
				break;

			case 'game_loser':
				$game = $games_table->field('name', ['Games.id' => $id]);
				$dependency = __('Loser of game {0}', $game);
				break;

			case 'seed':
				$dependency = __('{0} seed', Number::ordinal($id));
				break;

			case 'pool':
			case 'copy':
				$dependency = $pool->dependency();
				$alias = $pool->alias;
				if (!empty($alias)) {
					$dependency = "$alias [$dependency]";
				}
				break;
		}

		$this->{$type} = $dependency;
	}

	public function getAttendance(array $attendances, array $days): ?Attendance {
		if ($this->id) {
			return collection($attendances)->firstMatch(['game_id' => $this->id]);
		}

		$game_dates = GamesTable::matchDates($this->game_slot->game_date, $days);
		foreach ($game_dates as $date) {
			$record = collection($attendances)->firstMatch(['game_date' => $date]);
			if ($record) {
				return $record;
			}
		}

		return null;
	}
}
