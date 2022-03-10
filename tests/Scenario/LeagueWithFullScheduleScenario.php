<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\FranchiseFactory;
use App\Test\Factory\FranchisesTeamFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PoolFactory;
use App\Test\Factory\RegionFactory;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Factory\SpiritEntryFactory;
use App\Test\Factory\TeamFactory;
use Cake\Chronos\ChronosInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class LeagueWithFullScheduleScenario implements FixtureScenarioInterface {

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
		];

		if (array_key_exists('divisions', $args)) {
			$divisions = "[{$args['divisions']}]";
		} else {
			$divisions = '';
		}

		$open = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(3);
		/** @var League $league */
		$league = LeagueFactory::make(['open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true])
			->with("Divisions{$divisions}", [
				'open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true,
				'schedule_type' => 'ratings_ladder',
				'allstars' => 'optional', 'allstars_from' => 'opponent',
			])
			->with('Affiliates', $args['affiliate'] ?? [])
			->persist();

		// Where will the games be played?
		$fields = array_map(static function ($k) { return ['num' => $k]; }, range(1, count($league->divisions) * 2));
		/** @var \App\Model\Entity\Region $region */
		$region = RegionFactory::make(['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
			->with('Facilities.Fields', $fields)
			->persist();
		$fields = $region->facilities[0]->fields;
		$late_start = FrozenTime::createFromTime(21);
		$late_end = FrozenTime::createFromTime(23);

		$team_names = ['Red', 'Yellow', 'Green', 'Blue', 'Orange', 'Purple', 'Black', 'White'];
		$teams = array_map(function ($name, $seed) {
			return ['name' => $name, 'shirt_colour' => $name, 'initial_seed' => $seed];
		}, $team_names, range(1, count($team_names)));
		foreach ($league->divisions as $key => $division) {
			[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams = TeamFactory::make($teams)
				->with('Divisions', $division)->persist();
			DivisionsDayFactory::make(['day_id' => ChronosInterface::MONDAY, 'division_id' => $division->id])->persist();

			if (array_key_exists('coordinator', $args)) {
				DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $division->id])->persist();
			}

			// Week 1
			$game_slot = GameSlotFactory::make(['game_date' => $open, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $red->id, 'away_team_id' => $yellow->id,
					'home_score' => 17, 'away_score' => 5, 'rating_points' => 13, 'approved_by_id' => APPROVAL_AUTOMATIC,
				])
				->persist();
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $red->id, 'team_id' => $yellow->id, 'game_id' => $game_slot->games[0]->id],
					['created_team_id' => $yellow->id, 'team_id' => $red->id, 'game_id' => $game_slot->games[0]->id, 'q3' => 3, 'q5' => 3],
				])->persist();
			}

			GameSlotFactory::make(['game_date' => $open, 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $green->id, 'away_team_id' => $blue->id,
					'status' => 'cancelled',
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open, 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $orange->id, 'away_team_id' => $purple->id,
					'home_score' => 12, 'away_score' => 17, 'rating_points' => 10, 'approved_by_id' => APPROVAL_AUTOMATIC,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open, 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $black->id, 'away_team_id' => $white->id,
					'home_score' => 15, 'away_score' => 15, 'rating_points' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC,
				])
				->persist();

			// Week 2
			GameSlotFactory::make(['game_date' => $open->addWeek(), 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $red->id, 'away_team_id' => $green->id,
					'status' => 'home_default', 'home_score' => 0, 'away_score' => 6, 'approved_by_id' => APPROVAL_AUTOMATIC_HOME,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open->addWeek(), 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $yellow->id, 'away_team_id' => $blue->id,
					'status' => 'away_default', 'home_score' => 6, 'away_score' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC_AWAY,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open->addWeek(), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $orange->id, 'away_team_id' => $white->id,
					'home_score' => 17, 'away_score' => 12, 'rating_points' => 10, 'approved_by_id' => APPROVAL_AUTOMATIC,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open->addWeek(), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $black->id, 'away_team_id' => $purple->id,
					'home_score' => 15, 'away_score' => 15, 'rating_points' => 0, 'approved_by_id' => APPROVAL_AUTOMATIC,
				])
				->persist();

			// Week 3
			/** @var \App\Model\Entity\GameSlot $game_slot */
			$game_slot = GameSlotFactory::make(['game_date' => $open->addWeeks(2), 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $red->id, 'away_team_id' => $blue->id,
				])
				->persist();
			if ($args['scores']) {
				ScoreEntryFactory::make([
					['team_id' => $red->id, 'game_id' => $game_slot->games[0]->id, 'score_for' => 17, 'score_against' => 12],
					['team_id' => $blue->id, 'game_id' => $game_slot->games[0]->id, 'score_for' => 12, 'score_against' => 17],
				])->persist();
			}
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $red->id, 'team_id' => $blue->id, 'game_id' => $game_slot->games[0]->id],
					['created_team_id' => $blue->id, 'team_id' => $red->id, 'game_id' => $game_slot->games[0]->id, 'q3' => 3, 'q5' => 3],
				])->persist();
			}

			/** @var \App\Model\Entity\GameSlot $game_slot */
			$game_slot = GameSlotFactory::make(['game_date' => $open->addWeeks(2), 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $green->id, 'away_team_id' => $yellow->id,
				])
				->persist();
			if ($args['scores']) {
				ScoreEntryFactory::make([
					['team_id' => $green->id, 'game_id' => $game_slot->games[0]->id, 'score_for' => 15, 'score_against' => 14],
					['team_id' => $yellow->id, 'game_id' => $game_slot->games[0]->id, 'score_for' => 13, 'score_against' => 15],
				])->persist();
			}
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $green->id, 'team_id' => $yellow->id, 'game_id' => $game_slot->games[0]->id, 'q1' => 1, 'q5' => 1],
					['created_team_id' => $yellow->id, 'team_id' => $green->id, 'game_id' => $game_slot->games[0]->id, 'q3' => 1, 'q4' => 1],
				])->persist();
			}

			GameSlotFactory::make(['game_date' => $open->addWeeks(2), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $white->id, 'away_team_id' => $purple->id,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open->addWeeks(2), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $black->id, 'away_team_id' => $orange->id,
				])
				->persist();

			// Week 4
			/** @var \App\Model\Entity\GameSlot $game_slot */
			GameSlotFactory::make(['game_date' => $open->addWeeks(3), 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $red->id, 'away_team_id' => $green->id, 'published' => false,
				])
				->persist();
			if ($args['scores']) {
				ScoreEntryFactory::make([
					['team_id' => $red->id, 'game_id' => $game_slot->games[0]->id, 'score_for' => 5, 'score_against' => 4],
				])->persist();
			}
			if ($args['spirit']) {
				SpiritEntryFactory::make([
					['created_team_id' => $red->id, 'team_id' => $green->id, 'game_id' => $game_slot->games[0]->id, 'q3' => 1, 'q4' => 1],
				])->persist();
			}
			GameSlotFactory::make(['game_date' => $open->addWeeks(3), 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $yellow->id, 'away_team_id' => $blue->id, 'published' => false,
				])
				->persist();
			// Note: Adding more Monday games will likely break all the scheduling tests due to changes in standings and home/away ratios
			/*
			GameSlotFactory::make(['game_date' => $open->addWeeks(3), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $white->id, 'away_team_id' => $black->id,
				])
				->persist();
			GameSlotFactory::make(['game_date' => $open->addWeeks(3), 'game_start' => $late_start, 'game_end' => $late_end, 'assigned' => true])
				->with('Fields', $fields[$key * 2 + 1])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $purple->id, 'away_team_id' => $orange->id,
				])
				->persist();
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
				DivisionsDayFactory::make(['day_id' => ChronosInterface::MONDAY, 'division_id' => $playoffs->id])->persist();
				foreach ($franchises as $fkey => $franchise) {
					FranchisesTeamFactory::make(['franchise_id' => $franchise->id, 'team_id' => $playoffs->teams[$fkey]->id])->persist();
				}

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

				GameSlotFactory::make(['game_date' => $open->addWeeks(9), 'assigned' => true])
					->with('Fields', $fields[$key * 2])
					->with('Games', [
						// TODO: Fix some of these fields so they better reflect an actual playoff; add more games so there's a bracket to test with
						'division_id' => $playoffs->id, 'name' => 'A-1', 'type' => BRACKET_GAME,
						'home_dependency_type' => 'seed', 'home_dependency_id' => 1, 'away_dependency_type' => 'seed', 'away_dependency_id' => 2,
						'pool_id' => $pool->id, 'home_pool_team_id' => $pool->pools_teams[0]->id, 'away_pool_team_id' => $pool->pools_teams[7]->id,
					])
					->persist();

				$pool = PoolFactory::make(['division_id' => $playoffs->id, 'stage' => 2, 'name' => 'X', 'type' => 'crossover'])
					->persist();
			}
		}

		return $league;
	}
}
