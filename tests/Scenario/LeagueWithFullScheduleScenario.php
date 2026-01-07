<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Division;
use App\Model\Entity\Field;
use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Model\Entity\Person;
use App\Model\Entity\Team;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsGameslotFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\FranchiseFactory;
use App\Test\Factory\FranchisesTeamFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Factory\PoolFactory;
use App\Test\Factory\RegionFactory;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Factory\SpiritEntryFactory;
use App\Test\Factory\TeamFactory;
use Cake\Chronos\ChronosInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class LeagueWithFullScheduleScenario implements FixtureScenarioInterface {

	use ScenarioAwareTrait;

	private function addGame(Division $division, Team $home, Team $away, FrozenDate $date, Field $field, array $game_options = [], ?Person $official = null): Game {
		$slot_options = ['game_date' => $date, 'assigned' => true];
		if (array_key_exists('late', $game_options)) {
			$late_start = FrozenTime::createFromTime(21);
			$late_end = FrozenTime::createFromTime(23);
			$slot_options += ['game_start' => $late_start, 'game_end' => $late_end];
			unset($game_options['late']);
		}

		/** @var Game $game */
		$game = GameFactory::make(array_merge($game_options, [
			'division_id' => $division->id, 'home_team_id' => $home->id, 'away_team_id' => $away->id,
		]))
			->with('GameSlots', GameSlotFactory::make($slot_options)
				->with('Fields', $field)
			);

		if ($official) {
			$game = $game->with('Officials', $official);
		}

		$game = $game->persist();

		// Populate the expected game associations
		// @todo: Does this need to populate the divisions_gameslots table too?
		$game->game_slot->divisions = [$division];
		$game->home_team = $home;
		$game->away_team = $away;

		return $game;
	}

	/**
	 * Possible arguments are:
	 * - anything that LeagueScenario accepts
	 * - scores: bool
	 * - spirit: bool
	 * - playoffs: bool
	 * - additional_slots: int number of extra slots per week to create after the games already scheduled
	 * - additional_weeks: int number of extra weeks to create that many slots for, defaults to 1 if not specified
	 */
	public function load(...$args): League {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$args += [
			'scores' => false,
			'spirit' => false,
			'playoffs' => false,
			// Some schedule-based things will return different results if run before game time on the current date than
			// after game time. This will ensure we get a day number that's not the current day of the week, but matches
			// the possible values for ChronosInterface.
			'day_id' => date('w') + 1,
			'additional_slots' => 0,
			'additional_weeks' => 1,
			'official' => null,
		];

		/** @var League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, $args);

		// Where will the games be played?
		$fields = array_map(static function ($k) { return ['num' => $k]; }, range(1, count($league->divisions) * 2));
		/** @var \App\Model\Entity\Region $region */
		$region = RegionFactory::make(['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
			->with('Facilities.Fields', $fields)
			->persist();
		$fields = $region->facilities[0]->fields;

		$team_names = ['Red', 'Yellow', 'Green', 'Blue', 'Orange', 'Purple', 'Black', 'White'];
		$ratings = [1513, 1487, 1500, 1500, 1500, 1510, 1500, 1490];
		foreach ($league->divisions as $key => $division) {
			$teams = array_map(static function ($name, $rating, $seed) use ($division) {
				return ['name' => $name, 'shirt_colour' => $name, 'rating' => $rating, 'initial_seed' => $seed, 'division_id' => $division->id];
			}, $team_names, $ratings, range(1, count($team_names)));

			// We need a copy of the division entity without any associations loaded, to put into the game slot divisions array
			/** @var Division $division_copy */
			$division_copy = DivisionFactory::get($division->id);

			[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams = TeamFactory::make($teams)->persist();
			$open = $division->open;

			// Week 1
			$game = $division->games[] = $this->addGame($division_copy, $red, $yellow, $open, $fields[$key * 2],
				['home_score' => 17, 'away_score' => 5, 'rating_points' => 13, 'approved_by_id' => APPROVAL_AUTOMATIC],
				$args['official']
			);

			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $red->id, 'team_id' => $yellow->id, 'game_id' => $game->id],
					['created_team_id' => $yellow->id, 'team_id' => $red->id, 'game_id' => $game->id, 'q3' => 3, 'q5' => 3],
				])->persist();
			}

			$division->games[] = $this->addGame($division_copy, $green, $blue, $open, $fields[$key * 2 + 1],
				['status' => 'cancelled']
			);

			$division->games[] = $this->addGame($division_copy, $orange, $purple, $open, $fields[$key * 2],
				['home_score' => 12, 'away_score' => 17, 'rating_points' => 10, 'approved_by_id' => APPROVAL_AUTOMATIC, 'late' => true]
			);

			$division->games[] = $this->addGame($division_copy, $black, $white, $open, $fields[$key * 2 + 1],
				['home_score' => 15, 'away_score' => 15, 'rating_points' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC, 'late' => true]
			);

			// Week 2
			$division->games[] = $this->addGame($division_copy, $red, $green, $open->addWeeks(1), $fields[$key * 2],
				['status' => 'home_default', 'home_score' => 0, 'away_score' => 6, 'approved_by_id' => APPROVAL_AUTOMATIC_HOME]
			);

			$division->games[] = $this->addGame($division_copy, $yellow, $blue, $open->addWeeks(1), $fields[$key * 2 + 1],
				['status' => 'away_default', 'home_score' => 6, 'away_score' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC_AWAY]
			);

			$division->games[] = $this->addGame($division_copy, $orange, $white, $open->addWeeks(1), $fields[$key * 2],
				['home_score' => 17, 'away_score' => 12, 'rating_points' => 10, 'approved_by_id' => APPROVAL_AUTOMATIC, 'late' => true]
			);

			$division->games[] = $this->addGame($division_copy, $black, $purple, $open->addWeeks(1), $fields[$key * 2 + 1],
				['home_score' => 15, 'away_score' => 15, 'rating_points' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC, 'late' => true]
			);

			// Week 3
			$game = $division->games[] = $this->addGame($division_copy, $red, $blue, $open->addWeeks(2), $fields[$key * 2]);

			if ($args['scores']) {
				ScoreEntryFactory::make([
					['team_id' => $red->id, 'game_id' => $game->id, 'score_for' => 17, 'score_against' => 12],
					['team_id' => $blue->id, 'game_id' => $game->id, 'score_for' => 12, 'score_against' => 17],
				])->persist();
			}
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $red->id, 'team_id' => $blue->id, 'game_id' => $game->id],
					['created_team_id' => $blue->id, 'team_id' => $red->id, 'game_id' => $game->id, 'q3' => 3, 'q5' => 3],
				])->persist();
			}

			$game = $division->games[] = $this->addGame($division_copy, $green, $yellow, $open->addWeeks(2), $fields[$key * 2 + 1]);

			if ($args['scores']) {
				ScoreEntryFactory::make([
					['team_id' => $green->id, 'game_id' => $game->id, 'score_for' => 15, 'score_against' => 14],
					['team_id' => $yellow->id, 'game_id' => $game->id, 'score_for' => 13, 'score_against' => 15],
				])->persist();
			}
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $green->id, 'team_id' => $yellow->id, 'game_id' => $game->id, 'q1' => 1, 'q5' => 1],
					['created_team_id' => $yellow->id, 'team_id' => $green->id, 'game_id' => $game->id, 'q3' => 1, 'q4' => 1],
				])->persist();
			}

			$division->games[] = $this->addGame($division_copy, $white, $purple, $open->addWeeks(2), $fields[$key * 2],
				['late' => true]
			);

			$division->games[] = $this->addGame($division_copy, $black, $orange, $open->addWeeks(2), $fields[$key * 2 + 1],
				['late' => true]
			);

			// Week 4
			$division->games[] = $this->addGame($division_copy, $red, $green, $open->addWeeks(3), $fields[$key * 2],
				['published' => false]
			);

			$division->games[] = $this->addGame($division_copy, $yellow, $blue, $open->addWeeks(3), $fields[$key * 2 + 1],
				['published' => false]
			);

			// Note: Adding more Monday games will likely break all the scheduling tests due to changes in standings and home/away ratios
			/*
			$division->games[] = $this->addGame($division_copy, $white, $black, $open->addWeeks(3), $fields[$key * 2],
				['late' => true]
			);

			$division->games[] = $this->addGame($division_copy, $purple, $orange, $open->addWeeks(3), $fields[$key * 2 + 1],
				['late' => true]
			);
			*/

			// Playoffs
			if ($args['playoffs']) {
				// Playoff team connections require franchises
				$franchises = FranchiseFactory::make(array_map(function ($name) { return ['name' => $name]; }, $team_names))
					->persist();
				foreach ($franchises as $fkey => $franchise) {
					FranchisesTeamFactory::make(['franchise_id' => $franchise->id, 'team_id' => $division->teams[$fkey]->id])->persist();
				}

				/** @var \App\Model\Entity\Division $playoffs */
				$league->divisions[] = $playoffs = DivisionFactory::make([
					'league_id' => $league->id, 'open' => $open->addWeeks(9), 'close' => $open->addWeeks(11), 'is_open' => true,
					'schedule_type' => 'tournament',
				])
					->inPlayoff()
					->persist();

				[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $playoffs->teams = TeamFactory::make($teams)
					->with('Divisions', $playoffs)->persist();
				foreach ($franchises as $fkey => $franchise) {
					FranchisesTeamFactory::make(['franchise_id' => $franchise->id, 'team_id' => $playoffs->teams[$fkey]->id])->persist();
				}

				// TODO: Can we do these bits in common with LeagueScenario?
				DivisionsDayFactory::make(['day_id' => $args['day_id'], 'division_id' => $playoffs->id])->persist();
				if (array_key_exists('coordinator', $args)) {
					DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $playoffs->id])->persist();
				}

				$pool = PoolFactory::make(['division_id' => $playoffs->id])
					->with('PoolsTeams', [
						['alias' => 'A1', 'dependency_pool_id' => 1, 'dependency_id' => 1, 'team_id' => $red->id],
						['alias' => 'A2', 'dependency_pool_id' => 1, 'dependency_id' => 2, 'team_id' => $blue->id],
						['alias' => 'A3', 'dependency_pool_id' => 1, 'dependency_id' => 3, 'team_id' => $yellow->id],
						['alias' => 'A4', 'dependency_pool_id' => 1, 'dependency_id' => 4, 'team_id' => $green->id],
						['alias' => 'A5', 'dependency_pool_id' => 1, 'dependency_id' => 5, 'team_id' => $black->id],
						['alias' => 'A6', 'dependency_pool_id' => 1, 'dependency_id' => 6, 'team_id' => $white->id],
						['alias' => 'A7', 'dependency_pool_id' => 1, 'dependency_id' => 7, 'team_id' => $purple->id],
						['alias' => 'A8', 'dependency_pool_id' => 1, 'dependency_id' => 8, 'team_id' => $orange->id],
					])
					->persist();

				$division->games[] = GameFactory::make([
					// TODO: Fix some of these fields so they better reflect an actual playoff; add more games so there's a bracket to test with
					'division_id' => $playoffs->id, 'name' => 'A-1', 'type' => BRACKET_GAME,
					'home_dependency_type' => 'seed', 'home_dependency_id' => 1, 'away_dependency_type' => 'seed', 'away_dependency_id' => 2,
					'pool_id' => $pool->id, 'home_pool_team_id' => $pool->pools_teams[0]->id, 'away_pool_team_id' => $pool->pools_teams[7]->id,
				])
					->with('GameSlots', GameSlotFactory::make(['game_date' => $open->addWeeks(9), 'assigned' => true])
						->with('Fields', $fields[$key * 2])
					)
					->persist();

				PoolFactory::make(['division_id' => $playoffs->id, 'stage' => 2, 'name' => 'X', 'type' => 'crossover'])
					->persist();
			}

			for ($week = 0; $week < $args['additional_weeks']; ++ $week) {
				for ($i = 0; $i < $args['additional_slots']; ++ $i) {
					$slot = GameSlotFactory::make([
						'game_date' => $open->addweeks(4 + $week),
						'game_start' => FrozenTime::createFromTime($i),
						'game_end' => FrozenTime::createFromTime($i + 1),
					])
						->with('Fields', $fields[$key * 2])
						->persist();
					DivisionsGameslotFactory::make(['division_id' => $division->id, 'game_slot_id' => $slot->id])->persist();
				}
			}
		}

		return $league;
	}
}
