<?php
/**
 * Base class for sport-specific functionality, primarily for stat-tracking.
 * This class defines default functions for some common stats that multiple
 * sports need, as well as providing some common utility functions that
 * derived classes need.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\Stat;
use App\Model\Entity\StatType;
use App\Model\Entity\Team;
use App\Model\Table\StatsTable;
use App\Service\Games\ScoreService;
use Cake\ORM\TableRegistry;

class Sport {
	protected array $rosters = [];
	/**
	 * @var StatType[]
	 */
	protected array $stat_types = [];
	protected array $stats = [];
	protected ?StatsTable $Stats = null;

	public function validatePlay(Team $team, string $play, int $score_from, array $details) {
		switch ($play) {
			case 'Start':
				if (!empty($details)) {
					return __('Game timer was already initialized.');
				}
		}
		return true;
	}

	/*
	 * Default functions for how many points the various outcomes are worth.
	 */
	public function winValue(): int {
		return 2;
	}

	public function tieValue(): int {
		return 1;
	}

	public function lossValue(): int {
		return 0;
	}

	/**
	 * @param Stat[] $stats
	 * @param StatType[] $stat_types
	 * @return array
	 */
	public function calculateStats(array $stats, array $stat_types): array {
		$this->initRostersFromStats($stats);
		$this->initStats($stats);
		$calculated = new \ArrayObject();

		foreach ($stat_types as $stat_type) {
			switch ($stat_type->type) {
				case 'season_total':
					$this->seasonTotal($stat_type, $calculated);
					break;
				case 'season_avg':
					$this->seasonAvg($stat_type, $calculated);
					break;
				case 'season_calc':
					$func = "{$stat_type->handler}_season";
					if (method_exists($this, $func)) {
						$this->$func($stat_type, $calculated);
					} else {
						trigger_error("Season stat handler {$stat_type->handler} was not found in the {$stat_type->sport} component!", E_USER_ERROR);
					}
					break;
			}
		}

		if (!empty($calculated)) {
			return $calculated->getArrayCopy();
		} else {
			return [];
		}
	}

	protected function initRostersFromGame($game) {
		if (!empty($this->rosters)) {
			return;
		}

		$this->rosters = [];
		foreach ([$game->home_team, $game->away_team] as $team) {
			$this->rosters[$team->id] = collection($team->people)->combine('id', '_joinData.position')->toArray();
			$players = array_unique(collection($game->stats)->match(['team_id' => $team->id])->extract('person_id')->toArray());

			// Add subs, if any, as unspecified positions
			foreach ($players as $player) {
				if (!array_key_exists($player, $this->rosters[$team->id])) {
					$this->rosters[$team->id][$player] = 'unspecified';
				}
			}
		}

		$this->Stats = TableRegistry::getTableLocator()->get('Stats');
	}

	/**
	 * @param Stat[] $stats
	 * @return void
	 */
	protected function initRostersFromStats(array $stats): void {
		if (!empty($this->rosters)) {
			return;
		}

		$teams = array_unique(collection($stats)->extract('team_id')->toArray());
		$this->rosters = [];
		$roster_table = TableRegistry::getTableLocator()->get('TeamsPeople');
		foreach ($teams as $team) {
			$players = array_unique(collection($stats)->match(['team_id' => $team])->extract('person_id')->toArray());
			$this->rosters[$team] = $roster_table->find('list', [
				'conditions' => [
					'team_id' => $team,
					'person_id IN' => $players,
				],
				'keyField' => 'person_id',
				'valueField' => 'position',
			])->toArray();

			// Add subs, if any, as unspecified positions
			foreach ($players as $player) {
				if (!array_key_exists($player, $this->rosters[$team])) {
					$this->rosters[$team][$player] = 'unspecified';
				}
			}
		}
	}

	/**
	 * @param Stat[] $stats
	 * @return void
	 */
	public function initStats(array $stats): void {
		$this->stats = [];
		foreach ($stats as $stat) {
			if (!array_key_exists($stat->person_id, $this->stats)) {
				$this->stats[$stat->person_id] = ['stats' => [], 'games' => []];
			}
			if (!array_key_exists($stat->stat_type_id, $this->stats[$stat->person_id]['stats'])) {
				$this->stats[$stat->person_id]['stats'][$stat->stat_type_id] = [];
			}
			$this->stats[$stat->person_id]['stats'][$stat->stat_type_id][] = $stat->value;
			$this->stats[$stat->person_id]['games'][$stat->game_id] = true;
		}
	}

	protected function initStatTypes(): void {
		if (!empty($this->stat_types)) {
			return;
		}

		$stat_types_table = TableRegistry::getTableLocator()->get('StatTypes');

		// "entered" stat types take priority
		$this->stat_types = $stat_types_table->find()
			->where([
				'sport' => $this->sport,
				'type' => 'entered',
			])
			->all()
			->indexBy('internal_name')
			->toArray();

		// Add "game_calc" stats for internal names not already covered
		$this->stat_types += $stat_types_table->find()
			->where([
				'sport' => $this->sport,
				'type' => 'game_calc',
				'NOT' => ['internal_name IN' => array_keys($this->stat_types)],
			])
			->all()
			->indexBy('internal_name')
			->toArray();
	}

	protected function statType(string $stat_name): StatType {
		$this->initStatTypes();
		if (!array_key_exists($stat_name, $this->stat_types)) {
			trigger_error("Can't find stat type $stat_name in {$this->sport}!", E_USER_ERROR);
		}
		return $this->stat_types[$stat_name];
	}

	protected function value(StatType $stat_type, int $person_id, array $stats): float {
		// We can't use $this->stats to avoid the extra loop here, because this is called during submission
		// and $this->stats is set during later viewing. Overhead will be minimal, as the data set being
		// operated on during submission is quite small.
		$value = collection($stats)->firstMatch(['stat_type_id' => $stat_type->id, 'person_id' => $person_id]);
		if (empty($value)) {
			// Since we're only dealing with people that have had at least some stats entered here,
			// we consider missing values to be zeros that were just not entered.
			return 0;
		}
		return $value->value;
	}

	protected function gameSum(StatType $stat_type, Game $game, array $stat_names): void {
		$this->initRostersFromGame($game);
		$stat_types = [];
		foreach ($stat_names as $stat_name) {
			$stat_types[] = $this->statType($stat_name);
		}

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = 0;
				foreach ($stat_types as $type) {
					$value += $this->value($type, $person_id, $game->stats);
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	protected function gameRatio(StatType $stat_type, Game $game, StatType $numerator, StatType $denominator): void {
		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$d = $this->value($denominator, $person_id, $game->stats);
				if ($d) {
					$value = round($this->value($numerator, $person_id, $game->stats) / $denominator, 3);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	protected function gamePercent(StatType $stat_type, Game $game, StatType $numerator, StatType $denominator): void {
		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$d = $this->value($denominator, $person_id, $game->stats);
				if ($d) {
					$value = round($this->value($numerator, $person_id, $game->stats) * 100 / $denominator, 1);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	protected function valueSum(StatType $stat_type, $person_id): float {
		// Since we're only dealing with people that have had at least some stats entered here,
		// we consider missing values to be zeros that were just not entered.
		if (array_key_exists($stat_type->id, $this->stats[$person_id]['stats'])) {
			if (empty($stat_type->sum_function)) {
				return array_sum($this->stats[$person_id]['stats'][$stat_type->id]);
			} else {
				return $this->{$stat_type->sum_function}($this->stats[$person_id]['stats'][$stat_type->id]);
			}
		}

		return 0;
	}

	protected function seasonTotal(StatType $stat_type, \ArrayObject $calculated): void {
		$base_type = $this->statType($stat_type->base);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->valueSum($base_type, $person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	protected function seasonAvg(StatType $stat_type, \ArrayObject $calculated): void {
		$base_type = $this->statType($stat_type->base);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = round($this->valueSum($base_type, $person_id) / $this->gamesPlayed($person_id), 1);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	protected function seasonRatio(StatType $stat_type, \ArrayObject $calculated, StatType $numerator, StatType $denominator): void {
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$d = $this->valueSum($denominator, $person_id);
				if ($d) {
					$value = round($this->valueSum($numerator, $person_id) / $d, 3);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	protected function seasonPercent(StatType $stat_type, \ArrayObject $calculated, StatType $numerator, StatType $denominator): void {
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$d = $this->valueSum($denominator, $person_id);
				if ($d) {
					$value = round($this->valueSum($numerator, $person_id) * 100 / $d, 1);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	protected function gamesPlayed(int $person_id): int {
		return count($this->stats[$person_id]['games']);
	}

	/**
	 * For most sports, all players can be given a count of the wins, losses and ties they participated in.
	 * Sports like hockey and baseball where a specific goalie or pitcher will be credited with the win or
	 * loss will need to override these functions or specify a different handler.
	 */

	public function wins_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->isWin($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (StatsTable::applicable($stat_type, $position)) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	public function winsGameRecalculate(StatType $stat_type, Game $game): void {
		foreach (['home_team_id', 'away_team_id'] as $team) {
			TableRegistry::getTableLocator()->get('Stats')->updateAll(
				['value' => $this->isWin($game, $game->{$team})],
				['stat_type_id' => $stat_type->id, 'game_id' => $game->id, 'team_id' => $game->{$team}]
			);
		}
	}

	public function wins_season(StatType $stat_type, \ArrayObject $calculated): void {
		$win_type = $this->statType('Wins');
		$tie_type = $this->statType('Ties');
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				// TODO: Make this "2" configurable for soccer, etc.
				$value = sprintf('%.03f', ($this->valueSum($win_type, $person_id) +
					$this->valueSum($tie_type, $person_id) / 2) /
					$this->gamesPlayed($person_id));
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	public function games_season(StatType $stat_type, \ArrayObject $calculated): void {
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->gamesPlayed($person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type->id] = $value;
				}
			}
		}
	}

	protected function isWin(Game $game, int $team_id): int {
		if ($game->isFinalized()) {
			if (($team_id == $game->home_team_id && $game->home_score > $game->away_score) ||
				($team_id == $game->away_team_id && $game->away_score > $game->home_score))
			{
				return 1;
			} else {
				return 0;
			}
		}

		$score_service = new ScoreService($game->score_entries ?? []);
		$entry = $score_service->getScoreEntryFrom($team_id);
		if ($entry && $entry->person_id) {
			// Use our score entry
			if ($entry->status != 'in_progress' && $entry->score_for > $entry->score_against) {
				return 1;
			} else {
				return 0;
			}
		}

		$opponent_id = ($game->home_team_id == $team_id ? $game->away_team_id : $game->home_team_id);
		$entry = $score_service->getScoreEntryFrom($opponent_id);
		if ($entry && $entry->person_id) {
			// Use opponent's score entry
			if ($entry->status != 'in_progress' && $entry->score_for < $entry->score_against) {
				return 1;
			} else {
				return 0;
			}
		}

		// Return a 0 and trust that it will be corrected later
		return 0;
	}

	public function losses_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->isLoss($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (StatsTable::applicable($stat_type, $position)) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	public function lossesGameRecalculate(StatType $stat_type, Game $game): void {
		foreach (['home_team_id', 'away_team_id'] as $team) {
			TableRegistry::getTableLocator()->get('Stats')->updateAll(
				['value' => $this->isLoss($game, $game->{$team})],
				['stat_type_id' => $stat_type->id, 'game_id' => $game->id, 'team_id' => $game->{$team}]
			);
		}
	}

	protected function isLoss(Game $game, int $team_id): int {
		if ($game->isFinalized()) {
			if (($team_id == $game->home_team_id && $game->home_score < $game->away_score) ||
				($team_id == $game->away_team_id && $game->away_score < $game->home_score))
			{
				return 1;
			} else {
				return 0;
			}
		}

		$score_service = new ScoreService($game->score_entries ?? []);
		$entry = $score_service->getScoreEntryFrom($team_id);
		if ($entry && $entry->person_id) {
			// Use our score entry
			if ($entry->status != 'in_progress' && $entry->score_for < $entry->score_against) {
				return 1;
			} else {
				return 0;
			}
		}

		$opponent_id = ($game->home_team_id == $team_id ? $game->away_team_id : $game->home_team_id);
		$entry = $score_service->getScoreEntryFrom($opponent_id);
		if ($entry && $entry->person_id) {
			// Use opponent's score entry
			if ($entry->status != 'in_progress' && $entry->score_for > $entry->score_against) {
				return 1;
			} else {
				return 0;
			}
		}

		// Return a 0 and trust that it will be corrected later
		return 0;
	}

	public function ties_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->isTie($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (StatsTable::applicable($stat_type, $position)) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	public function tiesGameRecalculate(StatType $stat_type, Game $game): void {
		foreach (['home_team_id', 'away_team_id'] as $team) {
			TableRegistry::getTableLocator()->get('Stats')->updateAll(
				['value' => $this->isTie($game, $game->{$team})],
				['stat_type_id' => $stat_type->id, 'game_id' => $game->id, 'team_id' => $game->{$team}]
			);
		}
	}

	protected function isTie(Game $game, int $team_id): int {
		if ($game->isFinalized()) {
			if ($game->home_score == $game->away_score) {
				return 1;
			} else {
				return 0;
			}
		}

		foreach ($game->score_entries as $entry) {
			if ($entry->status != 'in_progress' && $entry->score_for == $entry->score_against) {
				return 1;
			} else {
				return 0;
			}
		}

		// Return a 0 and trust that it will be corrected later
		return 0;
	}

	/**
	 *
	 * Sum functions
	 *
	 */

	public function null_sum(): string {
		return '';
	}

	public function minutes_sum(array $minutes): string {
		$ret = 0;
		foreach ($minutes as $m) {
			if (strpos($m, '.') !== false) {
				[$m,$s] = explode('.', $m);
			} else {
				$s = 0;
			}
			$ret += $m * 60 + $s;
		}
		return sprintf('%d.%02d', floor($ret / 60), $ret % 60);
	}

	/**
	 *
	 * Formatter functions
	 *
	 */

	public function minutes_format(float $value): string {
		$minutes = floor($value);
		$seconds = floor(($value - $minutes) * 100);
		return sprintf('%d:%02d', $minutes, $seconds);
	}

	/**
	 *
	 * Validation helpers
	 *
	 */

	public function validate_team_score(StatType $stat): array {
		$ret = [];
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() > team_score) alert_msg += 'The number of {$stat->name} entered is more than the score.\\n';";
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() < team_score) confirm_msg += 'The number of {$stat->name} entered is less than the score.\\n';";
		return $ret;
	}

	public function validate_team_score_fuzzy(StatType $stat): array {
		$ret = [];
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() > team_score) confirm_msg += 'The number of {$stat->name} entered is more than the score.\\n';";
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() < team_score) confirm_msg += 'The number of {$stat->name} entered is less than the score.\\n';";
		return $ret;
	}

	public function validate_team_score_two(StatType $stat): array {
		$ret = [];
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() > team_score * 2) alert_msg += 'The number of {$stat->name} entered is more than the score.\\n';";
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() < team_score * 2) confirm_msg += 'The number of {$stat->name} entered is less than the score.\\n';";
		return $ret;
	}

	public function validate_opponent_score(StatType $stat): array {
		$ret = [];
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() > opponent_score) alert_msg += 'The number of {$stat->name} entered is more than the score.\\n';";
		$ret[] = "if (zjQuery('#team_' + team_id).find('th.stat_{$stat->id}').html() < opponent_score) confirm_msg += 'The number of {$stat->name} entered is less than the score.\\n';";
		return $ret;
	}

}
