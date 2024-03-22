<?php
/**
 * Base class for division-specific functionality.  This class defines default
 * no-op functions for all operations that divisions might need to do, as well
 * as providing some common utility functions that derived classes need.
 */
namespace App\Module;

use App\Model\Entity\Division;
use App\Model\Entity\Game;
use App\Model\Entity\GameSlot;
use App\Model\Entity\League;
use App\Model\Entity\Pool;
use Authorization\IdentityInterface;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Core\ModuleRegistry;
use App\Exception\ScheduleException;
use App\Model\Entity\Team;
use App\Model\Results\Comparison;
use App\Model\Table\GamesTable;

abstract class LeagueType {
	/**
	 * Define the element to use for rendering various views
	 */
	public $render_element = 'rounds';

	/**
	 * Temporary list of games to create
	 */
	protected $games = [];

	/**
	 * Return any league-type-specific links.
	 * By default, there are no extra links.
	 *
	 * @param mixed $division Entity containing the division data
	 * @param \Authorization\IdentityInterface $identity Identity to use for checking link permissions
	 * @param string $controller The current controller
	 * @param string $action The current action; links are not output if they match the controller and action
	 * @return array
	 */
	public function links(Division $division, IdentityInterface $identity = null, $controller, $action) {
		return [];
	}

	/**
	 * Generate a list of extra league-type-specific edit/display fields, as
	 * field => details pairs.  Details are arrays with keys like label (mandatory)
	 * and any options to be passed to the html->input call.
	 * Titles are in English, and will be translated in the view.
	 * By default, there are no extra fields.
	 *
	 * @return mixed An array containing the extra fields
	 *
	 */
	public function schedulingFields($administrative) {
		return [];
	}

	/**
	 * Check integrity of league-type-specific data.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be checked.
	 * @return bool
	 */
	public function schedulingFieldsRules(EntityInterface $entity) {
		return true;
	}

	/**
	 * Add any league-type-specific fields for new teams.
	 */
	public function newTeam() {
		return [];
	}

	/**
	 * Read everything we need for standings and schedules, from the cache or database.
	 */
	public function addResults(Division $division, $spirit_obj) {
		// Hopefully, everything we need is already cached
		$results = Cache::remember("division_{$division->id}_standings", function () use ($division, $spirit_obj) {
			$divisions_table = TableRegistry::getTableLocator()->get('Divisions');

			// Read the team list
			$divisions_table->loadInto($division, [
				'Teams' => [
					'queryBuilder' => function (Query $q) use ($division) {
						return $q
							->where(['Teams.division_id' => $division->id])
							->order(['Teams.seed']);
					},
					'TeamsPeople' => ['People' => ['Skills']],
				],
			]);

			// Find all games played by teams that are currently in this division,
			// or tournament games for this division
			if (!empty($division->teams)) {
				$team_ids = collection($division->teams)->extract('id')->toArray();
			} else {
				$team_ids = [];
			}

			$games = $divisions_table->Games
				->find('schedule', ['teams' => $team_ids, 'playoff_division' => $division->id])
				->find('played')
				->contain([
					'SpiritEntries',
				])
				->order(['GameSlots.game_date', 'GameSlots.game_start'])
				->toArray();
			if (empty($games)) {
				$division->_results = [];
			} else {
				// Sort games by date, time and field. Field makes no difference to these results, but whatever.
				usort($games, [GamesTable::class, 'compareDateAndField']);
				GamesTable::adjustEntryIndices($games);
			}

			$this->sort($division, $division->league, $games, $spirit_obj, false);

			// Save updated seed information
			$seed = 0;
			foreach ($division->teams as $team) {
				$team->seed = ++$seed;
			}
			$divisions_table->Teams->saveMany($division->teams);

			return ['results' => $division->_results, 'teams' => $division->teams];
		}, 'long_term');

		if (!$results) {
			return false;
		}

		$division->_results = $results['results'];
		$division->teams = $results['teams'];
		return true;
	}

	/**
	 * Sort the provided teams according to division-specific criteria.
	 */
	public function sort(Division $division, League $league, $games, Spirit $spirit_obj = null, $include_tournament = true) {
		if (!empty($games)) {
			if (!$spirit_obj && $league->hasSpirit()) {
				$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$league->sotg_questions}");
			}
			$this->presort($division, $league, $games, $spirit_obj);
		}

		// If all teams have seeds set, we trust them
		if (!collection($division->teams)->some(function ($team) { return $team->seed == 0; })) {
			$division->teams = collection($division->teams)->sortBy('seed', SORT_ASC)->toArray();
		} else if ($division->schedule_type === 'tournament' || $include_tournament) {
			\App\Lib\context_uasort($division->teams, ['App\Model\Results\Comparison', 'compareTeamsTournament'], ['league_obj' => $this]);
		} else {
			$sort_context = ['results' => 'season', 'current_round' => $division->current_round, 'tie_breaker' => $league->tie_breakers];
			\App\Lib\context_uasort($division->teams, [$this, 'compareTeams'], $sort_context);
			if (!empty($games)) {
				Comparison::detectAndResolveTies($division->teams, [$this, 'compareTeams'], $sort_context);
			}
		}
		$division->teams = collection($division->teams)->indexBy('id')->toArray();
	}

	/**
	 * Do any calculations that will make the comparisons more efficient, such
	 * as determining wins, losses, spirit, etc.
	 */
	protected function presort(Division $division, League $league, $games, Spirit $spirit_obj = null) {
		$sport_obj = ModuleRegistry::getInstance()->load("Sport:{$league->sport}");
		$division->teams = collection($division->teams)->indexBy('id')->toArray();

		foreach ($games as $game) {
			if (!in_array($game->status, Configure::read('unplayed_status'))) {
				$division->addGameResult($game);
				if ($game->home_team_id) {
					// When there are cross-divisional games, or certain other rare situations, the team record may not be in this division's list
					if (array_key_exists($game->home_team_id, $division->teams)) {
						$division->teams[$game->home_team_id]->addGameResult($game, $league, $spirit_obj, $sport_obj);
					}
				}
				if ($game->away_team_id) {
					if (array_key_exists($game->away_team_id, $division->teams)) {
						$division->teams[$game->away_team_id]->addGameResult($game, $league, $spirit_obj, $sport_obj);
					}
				}
			}
		}

		$division->_results->finalize();
	}

	/**
	 * By default, we sort by any seeding information we may have, and then by name as a last resort.
	 */
	public static function compareTeams(Team $a, Team $b, array $context) {
		if ($a->initial_seed < $b->initial_seed) {
			return -1;
		} else if ($a->initial_seed > $b->initial_seed) {
			return 1;
		}

		return (strtolower($a->name) > strtolower($b->name));
	}

	/**
	 * Get a preview of the games to be created. Most schedule types will have no preview.
	 *
	 * @param mixed $division The division to do the preview for
	 * @param mixed $pool The pool of the tournament we're scheduling (if any)
	 * @param mixed $type The scheduling type to return the description of
	 * @param mixed $num_teams The number of teams the schedule will be for
	 * @return mixed The preview
	 *
	 */
	public function schedulePreview(Division $division, Pool $pool = null, $type, $num_teams) {
		return null;
	}

	/**
	 * Returns the list of options for scheduling games in this type of division.
	 *
	 * @return mixed An array containing the list of scheduling options.
	 */
	public function scheduleOptions($num_teams, $stage, $sport) {
		return [];
	}

	/**
	 * Get the description of a scheduling type.
	 *
	 * @param mixed $type The scheduling type to return the description of
	 * @param mixed $num_teams The number of teams to include in the description
	 * @param mixed $stage The stage of the tournament we're scheduling
	 * @param mixed $sport The sport we're scheduling for
	 * @return mixed The description
	 *
	 */
	public function scheduleDescription($type, $num_teams, $stage, $sport) {
		if ($type === 'crossover') {
			return __('crossover game');
		}
		$types = $this->scheduleOptions($num_teams, $stage, $sport);
		$desc = $types[$type];
		return $desc;
	}

	/**
	 * Return the requirements of a particular scheduling type.  This is
	 * just a default stub, overloaded by specific algorithms.
	 *
	 * @param mixed $num_teams The number of teams to schedule for
	 * @param mixed $type The schedule type
	 * @return mixed An array with the number of fields needed each day
	 *
	 */
	public function scheduleRequirements($type, $num_teams) {
		return [];
	}

	public function canSchedule($required_field_counts, $available_field_counts) {
		foreach ($required_field_counts as $required) {
			while ($required > 0) {
				if (empty($available_field_counts)) {
					return false;
				}
				// TODO: Build a summary of the assignments as we go, so we can throw an exception with more useful
				// feedback if we run short. Same stuff needs to be implemented in LeagueTypeTournament.
				$required -= array_shift($available_field_counts);
			}
		}

		return true;
	}

	/**
	 * Load everything required for scheduling.
	 */
	public function startSchedule(Division $division, $start_date) {
		TableRegistry::getTableLocator()->get('Divisions')->loadInto($division, [
			'Days' => [
				'queryBuilder' => function (Query $q) {
					return $q->order(['DivisionsDays.day_id']);
				},
			],
			'Teams' => [
				'queryBuilder' => function (Query $q) use ($division) {
					if (!empty($division->_options->exclude)) {
						$q->where(['NOT' => ['Teams.id IN' => $division->_options->exclude]]);
					}
					return $q->order('Teams.name');
				},
				'Facilities',
			],
			'Games' => [
				'queryBuilder' => function (Query $q) use ($division) {
					$q->where(['NOT' => ['Games.status IN' => ['cancelled', 'rescheduled']]]);
					if (!empty($division->_options->ignore_games)) {
						$q->andWhere(['Games.id NOT IN' => collection($division->_options->ignore_games)->extract('id')->toList()]);
					}
					return $q;
				},
				'GameSlots',
			],
			'GameSlots' => [
				'queryBuilder' => function (Query $q) use ($division, $start_date) {
					$q->where(['game_date >=' => $start_date]);
					if (empty($division->_options->double_booking)) {
						$q->where(['GameSlots.assigned' => false]);
					}

					return $q->order(['GameSlots.game_date', 'GameSlots.game_start']);
				},
				'Fields' => ['Facilities'],
				'Divisions',
			],
		]);
		$division->used_slots = [];

		// Go through all the games and count the number of home and away games
		// and games with preferences for each team
		$division->home_games = $division->away_games = $division->field_rank_sum = [];
		foreach ($division->games as $game) {
			if (!array_key_exists($game->home_team_id, $division->home_games)) {
				$division->home_games[$game->home_team_id] = 1;
			} else {
				++ $division->home_games[$game->home_team_id];
			}

			if (!empty($game->away_team_id)) {
				if (!array_key_exists($game->away_team_id, $division->away_games)) {
					$division->away_games[$game->away_team_id] = 1;
				} else {
					++ $division->away_games[$game->away_team_id];
				}
			}

			if (!array_key_exists($game->home_team_id, $division->field_rank_sum)) {
				$division->field_rank_sum[$game->home_team_id] = 0;
			}
			if ($game->home_field_rank === NULL) {
				// A NULL home rank means that the home team had no preference at that time,
				// which means we count it as being 100% satisfied.
				++$division->field_rank_sum[$game->home_team_id];
			} else if ($game->home_field_rank != 0) {
				// A zero rank adds nothing to the sum
				$division->field_rank_sum[$game->home_team_id] += 1 / $game->home_field_rank;
			}

			if (!empty($game->away_team_id) && !empty($game->away_field_rank)) {
				if (!array_key_exists($game->away_team_id, $division->field_rank_sum)) {
					$division->field_rank_sum[$game->away_team_id] = 0;
				}
				$division->field_rank_sum[$game->away_team_id] += 1 / $game->away_field_rank;
			}
		}
	}

	public function finishSchedule(Division $division, $games) {
		if (empty($games)) {
			throw new ScheduleException(__('No games were created.'));
		}

		$games_table = TableRegistry::getTableLocator()->get('Games');
		$games_table->getConnection()->transactional(function () use ($division, $games, $games_table) {
			foreach ($games as $game) {
				$validate = ($game->isNew() ? 'scheduleAdd' : 'scheduleEdit');
				if (!$games_table->save($game, ['validate' => $validate, 'games' => $games, 'game_slots' => $division->used_slots])) {
					$errors = [__('Failed to save a game.')];
					foreach ($game->getErrors() as $field => $error) {
						$errors[] = $field . ': ';
						foreach ($error as $message) {
							$errors[] = $message;
						}
					}
					throw new ScheduleException($errors);
				}
			}

			return true;
		});
	}

	/**
	 * Create a single game in this division
	 */
	public function createEmptyGame(Division $division, $date = null): Game {
		$num_teams = count($division->teams);

		if ($num_teams < 2) {
			throw new ScheduleException(__('Must have two teams.'));
		}

		$game = TableRegistry::getTableLocator()->get('Games')->newEntity([
			'type' => SEASON_GAME,
		], array_merge($division->_options->toArray(), [
			'validate' => 'scheduleAdd',
			'division' => $division,
		]));

		if ($date) {
			$game->game_slot = $this->selectRandomGameslot($division, [$date]);
			$game->game_slot_id = $game->game_slot->id;
		}

		return $game;
	}

	/**
	 * Schedule one set of games, using weighted field assignment
	 *
	 * @param mixed $division Entity containing the division data
	 * @param mixed $date The date of the games
	 * @param mixed $teams List of teams, sorted into pairs by matchup
	 * @param mixed $remaining The number of other games still to be scheduled after this set
	 * @return Game[] games created
	 *
	 */
	protected function createGamesForTeams(Division $division, $date, $teams, $remaining = 0) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = [];

		// Iterate over teams array pairwise and create games with balanced home/away
		$count = count($teams);
		for($team_idx = 0; $team_idx < $count; $team_idx += 2) {
			$games[] = $this->addTeamsBalanced($division, $teams[$team_idx], $teams[$team_idx + 1]);
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		return $this->assignFieldsByPreferences($division, $date, $games, $remaining);
	}

	/**
	 * Add two opponents to a game, attempting to balance the number of home
	 * and away games
	 */
	protected function addTeamsBalanced(Division $division, Team $a, Team $b) {
		$a_ratio = $this->homeAwayRatio($division, $a->id);
		$b_ratio = $this->homeAwayRatio($division, $b->id);

		// team with lowest ratio (fewer home games) gets to be home.
		if ($a_ratio < $b_ratio) {
			$home = $a;
			$away = $b;
		} elseif ($a_ratio > $b_ratio) {
			$home = $b;
			$away = $a;
		} else {
			// equal ratios... choose randomly.
			if (mt_rand(0, 1) > 0) {
				$home = $a;
				$away = $b;
			} else {
				$home = $b;
				$away = $a;
			}
		}

		if (!array_key_exists($home->id, $division->home_games)) {
			$division->home_games[$home->id] = 0;
		}
		if (!array_key_exists($away->id, $division->away_games)) {
			$division->away_games[$away->id] = 0;
		}

		++ $division->home_games[$home->id];
		++ $division->away_games[$away->id];

		$save = [
			'type' => SEASON_GAME,
			'home_team_id' => $home->id,
			'away_team_id' => $away->id,
		];
		$game = TableRegistry::getTableLocator()->get('Games')->newEntity($save,
			array_merge($division->_options->toArray(), [
				'validate' => 'scheduleAdd',
				'division' => $division,
			])
		);
		$game->home_team = $home;
		$game->away_team = $away;
		return $game;
	}

	protected function homeAwayRatio(Division $division, $id): float {
		if (array_key_exists($id, $division->home_games)) {
			$home_games = $division->home_games[$id];
		} else {
			$home_games = 0;
		}

		if (array_key_exists($id, $division->away_games)) {
			$away_games = $division->away_games[$id];
		} else {
			$away_games = 0;
		}

		if ($home_games + $away_games < 1) {
			// Avoid divide-by-zero
			return 0;
		}

		return ($home_games / ($home_games + $away_games));
	}

	/**
	 * Assign field based on home field or region preference.
	 *
	 * It uses the selectWeightedGameslot function, which first looks at home field
	 * designation, then at field region preferences.
	 *
	 * We first sort games in order of the home team's allocation preference ratio.
	 * Teams with a low ratio get first crack at a desired location. Games where the
	 * home team has a home field are first in the list, to prevent another team with
	 * a lower ratio from scooping another team's dedicated home field.
	 *
	 * Once sorted, we simply loop over all games and call selectWeightedGameslot(),
	 * which takes region preference into account.
	 *
	 * @return Game[] games created
	 */
	public function assignFieldsByPreferences(Division $division, $date, $games, $remaining = 0) {
		/*
		 * We sort by ratio of getting their preference, from lowest to
		 * highest, so that teams who received their field preference least
		 * will have a better chance of it.
		 */
		$division->teams = collection($division->teams)->indexBy('id')->toArray();
		usort($games, function ($a, $b) use ($division) {
			// Put all those games where one team has a home field at the top of the list
			$a_home = $this->hasHomeField($division, $a);
			$b_home = $this->hasHomeField($division, $b);
			if ($a_home && !$b_home) {
				return -1;
			}
			if (!$a_home && $b_home) {
				return 1;
			}

			$a_ratio = $this->preferredFieldRatio($division, $a);
			$b_ratio = $this->preferredFieldRatio($division, $b);

			return ($a_ratio > $b_ratio) ? 1 : (($a_ratio < $b_ratio) ? -1 : 0);
		});

		$return = [];
		while ($game = array_shift($games)) {
			$game->game_slot = $this->selectWeightedGameslot($division, $date, $game, count($games) + 1 + $remaining);
			$game->game_slot_id = $game->game_slot->id;
			$return[] = $game;
		}
		return $return;
	}

	protected function hasHomeField(Division $division, Game $game): bool {
		return (Configure::read('feature.home_field') && (
			($game->home_team_id && $division->teams[$game->home_team_id]->home_field_id) ||
			($game->away_team_id && $division->teams[$game->away_team_id]->home_field_id)
		));
	}

	protected function preferredFieldRatio(Division $division, Game $game): float {
		// If we're not using team preferences, that's like everyone
		// has 100% of their games in a preferred region.
		if (!Configure::read('feature.region_preference') && !Configure::read('feature.facility_preference')) {
			return 1;
		}

		// We've already dealt with games where a team has a home field. If
		// we're calling this function, then either both games being compared
		// involve a team with a home field, or neither does. So, if this
		// game has one, the other must also, in which case we want to look
		// to their opponents to break that tie. This tie-breaker will
		// only matter if multiple teams share a home field, but it doesn't
		// do any harm to include it in other situations.
		if (Configure::read('feature.home_field') && $division->teams[$game->home_team_id]->home_field_id) {
			$id = $game->away_team_id;
		} else {
			// We get here if home fields are not allowed, neither team
			// has a home field, or only the away team does. In any case,
			// it's the home team that we want to drive the preference.
			$id = $game->home_team_id;
		}

		// No preference means they're always happy.  We return over 100% to
		// force them to sort last when ordering by ratio, so that teams with
		// a preference always appear before them.
		if ((!Configure::read('feature.region_preference') || empty($division->teams[$id]->region_preference_id)) &&
			(!Configure::read('feature.facility_preference') || empty($division->teams[$id]->facilities)))
		{
			return 2;
		}

		if (!$division->teams[$id]->has('preferred_ratio')) {
			if (!array_key_exists($id, $division->field_rank_sum)) {
				$division->teams[$id]->preferred_ratio = 0;
			} else {
				$division->teams[$id]->preferred_ratio = $division->field_rank_sum[$id] /
					// We've already incremented these counters with the new game
					// before arriving here, so we subtract 1 to get the true count
					($division->home_games[$id] + $division->away_games[$id] - 1);
			}
		}
		return $division->teams[$id]->preferred_ratio;
	}

	/**
	 * Select a random game slot
	 *
	 * @param \App\Model\Entity\Division $division Entity containing the division data
	 * @param \Cake\I18n\FrozenDate[] $dates The possible dates of the game
	 * @param int $remaining The number of games still to be scheduled, including this one
	 *
	 */
	protected function selectRandomGameslot(Division $division, $dates, $remaining = 1, $recursive = false): GameSlot {
		$slots = [];
		foreach ($dates as $date) {
			$slots = array_merge($slots, collection($division->game_slots)->match(['game_date' => $date])->toList());
			// TODO: When we're not scheduling tournaments, perhaps we should include all possible dates,
			// to get a better distribution across multiple divisions with multi-night games. See also
			// matchingSlots.
			if (count($slots) >= $remaining) {
				break;
			}
		}

		if (empty($slots)) {
			// If double-booking is allowed, we can reset the list of slots and start again
			if (!$recursive && $division->_options->double_booking) {
				$division->game_slots = $division->used_slots;
				$division->used_slots = [];
				return $this->selectRandomGameslot($division, $dates, $remaining, true);
			}

			throw new ScheduleException(__('There are insufficient game slots available to complete this schedule. Check the {0} for details.'), [
				'class' => 'warning',
				'replacements' => [
					[
						'type' => 'link',
						'link' => __('{0} Availability Report', __(Configure::read("sports.{$division->league->sport}.field_cap"))),
						'target' => ['controller' => 'Divisions', 'action' => 'slots', '?' => ['division' => $division->id, 'date' => $dates[0]->toDateString()]],
					],
				],
			]);
		}

		shuffle($slots);
		$slot = reset($slots);
		$this->removeGameslot($division, $slot);
		return $slot;
	}

	/**
	 * Select an appropriate game slot for this game. "appropriate" takes
	 * field quality, home field designation, and field preferences into account.
	 * Game slot is to be selected from those available for the division in which
	 * this game exists.
	 *
	 * TODO: Take field quality into account when assigning.  Easiest way
	 * to do this would be to order by field quality instead of RAND(),
	 * keeping our best fields in use.
	 *
	 * @param mixed $division Entity containing the division data
	 * @param mixed $date The date of the game
	 * @param mixed $game Entity with game details (e.g. home_team_id, away_team_id)
	 * @param mixed $remaining The number of games still to be scheduled, including this one
	 */
	protected function selectWeightedGameslot(Division $division, $date, $game, $remaining): GameSlot {
		$slots = [];

		if (!empty($game->home_team_id)) {
			$home = $division->teams[$game->home_team_id];
		}
		if (!empty($game->away_team_id)) {
			$away = $division->teams[$game->away_team_id];
		}

		$days = collection($division->days)->extract('id')->toArray();
		$match_dates = GamesTable::matchDates($date, $days);

		if (Configure::read('feature.home_field')) {
			// Try to adhere to the home team's home field
			if (isset($home) && $home->home_field_id) {
				$slots = $this->matchingSlots($division, ['field_id' => $home->home_field_id], $match_dates, $remaining);
			}

			// If not available, try the away team's home field
			if (empty($slots) && isset($away) && $away->home_field_id) {
				$slots = $this->matchingSlots($division, ['field_id' => $away->home_field_id], $match_dates, $remaining);
			}
		}

		// Maybe try facility preferences
		if (empty($slots) && Configure::read('feature.facility_preference')) {
			if (isset($home) && !empty($home->facilities)) {
				foreach ($home->facilities as $facility) {
					$slots = $this->matchingSlots($division, ['field.facility_id' => $facility->id], $match_dates, $remaining);
					if (!empty($slots)) {
						break;
					}
				}
			}

			if (empty($slots) && isset($away) && !empty($away->facilities)) {
				foreach ($away->facilities as $facility) {
					$slots = $this->matchingSlots($division, ['field.facility_id' => $facility->id], $match_dates, $remaining);
					if (!empty($slots)) {
						break;
					}
				}
			}
		}

		// Maybe try region preferences
		if (empty($slots) && Configure::read('feature.region_preference')) {
			if (isset($home) && $home->region_preference_id) {
				$slots = $this->matchingSlots($division, ['field.facility.region_id' => $home->region_preference_id], $match_dates, $remaining);
			}

			if (empty($slots) && isset($away) && $away->region_preference_id) {
				$slots = $this->matchingSlots($division, ['field.facility.region_id' => $away->region_preference_id], $match_dates, $remaining);
			}
		}

		// If still nothing can be found, last try is just random
		if (empty($slots)) {
			return $this->selectRandomGameslot($division, $match_dates, $remaining);
		}

		shuffle($slots);
		$slot = reset($slots);
		$this->removeGameslot($division, $slot);
		return $slot;
	}

	protected function matchingSlots(Division $division, $criteria, $dates, $remaining) {
		$matches = [];
		foreach ($dates as $date) {
			$matches = array_merge($matches, collection($division->game_slots)->match(array_merge($criteria, ['game_date' => $date]))->toArray());
			// TODO: When we're not scheduling tournaments, perhaps we should include all possible dates,
			// to get a better distribution across multiple divisions with multi-night games. See also
			// selectRandomGameslot.
			if (count($matches) >= $remaining) {
				break;
			}
		}
		return $matches;
	}

	/**
	 * Remove a slot from the list of those available
	 *
	 * @param mixed $division Entity containing the division data
	 * @param mixed $slot Id of the slot to remove
	 *
	 */
	protected function removeGameslot(Division $division, $slot) {
		$division->used_slots[] = $slot;
		$division->game_slots = collection($division->game_slots)->filter(function ($s) use ($slot) {
			return $s->id != $slot->id;
		})->toList();
	}

	/**
	 * Count how many distinct game slot days are available from $date onwards
	 */
	protected function countAvailableGameslotDays(Division $division, $date, $slots_per_day) {
		$dates = array_unique(collection($division->game_slots)->filter(function ($slot) use ($date) {
			return $slot->game_date >= $date;
		})->extract('game_date')->toList());
		sort($dates);

		$available = $slots = 0;
		foreach ($dates as $date) {
			$slots += count(collection($division->game_slots)->match(['game_date' => $date])->toList());
			if ($slots >= $slots_per_day) {
				++$available;
				$slots = 0;
			}
		}
		return $available;
	}

	/**
	 * Return next available day of play after $date, based on game slot availability
	 *
	 * @return FrozenDate
	 */
	protected function nextGameslotDay(Division $division, $date, $skip) {
		if (!$skip) {
			// Leagues that operate on multiple nights of the week may have more
			// game slots available later in the same week, but we don't want to
			// use them.
			$days = collection($division->days)->extract('id')->toList();
			$match_dates = GamesTable::matchDates($date, $days);
			$last_date = max($match_dates);
			$dates = array_unique(collection($division->game_slots)->filter(function ($slot) use ($last_date) {
				return $slot->game_date > $last_date;
			})->extract('game_date')->toList());

			// Tournaments, on the other hand, will not want to do this. We detect
			// the difference by whether or not there are any more dates available.
			if (empty($dates)) {
				$dates = array_unique(collection($division->game_slots)->filter(function ($slot) use ($date) {
					return $slot->game_date > $date;
				})->extract('game_date')->toList());
			}

			if (empty($dates)) {
				return null;
			}
			return min($dates);
		}

		$dates = array_unique(collection($division->game_slots)->filter(function ($slot) use ($date) {
			return $slot->game_date >= $date;
		})->extract('game_date')->toList());
		sort($dates);
		while ($skip > 0 && !empty($dates)) {
			$date = array_shift($dates);
			$skip -= count(collection($division->game_slots)->match(['game_date' => $date])->toList());
		}
		return array_shift($dates);
	}

}
