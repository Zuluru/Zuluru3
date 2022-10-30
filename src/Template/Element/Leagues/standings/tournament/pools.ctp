<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $games \App\Model\Entity\Pool[][]
 * @type $teams \App\Model\Entity\Team[]
 * @type $can_edit boolean
 */

use App\Controller\AppController;
use App\Model\Results\Comparison;
use App\Model\Traits\DateTimeCombinator;

ksort($games);
$teams = collection($teams)->indexBy('id')->toArray();

foreach ($games as $stage_id => $stage):
	ksort($stage);
	foreach ($stage as $pool_id => $pool):
?>
<div class="pool-results">
	<h4><?php
		echo __('Pool {0}', $pool->games[0]->home_pool_team->pool->translateField('name'));
		if ($can_edit) {
			echo '&nbsp;';
			echo $this->Form->iconPostLink('delete_24.png',
				['controller' => 'Schedules', 'action' => 'delete', 'division' => $division->id, 'pool' => $pool->games[0]->home_pool_team->pool->id, 'return' => AppController::_return()],
				['alt' => __('Delete'), 'title' => __('Delete Pool Games')]);
			echo $this->Html->iconLink('initialize_24.png',
				['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'pool' => $pool->games[0]->home_pool_team->pool->id, 'return' => AppController::_return()],
				['alt' => __('Initialize'), 'title' => __('Initialize Schedule Dependencies')]);
			echo $this->Html->iconLink('reset_24.png',
				['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'pool' => $pool->games[0]->home_pool_team->pool->id, 'reset' => true, 'return' => AppController::_return()],
				['alt' => __('Reset'), 'title' => __('Reset Schedule Dependencies')]);
			// TODO: Add publish/unpublish links here
		}
	?></h4>
<?php
		// Set the list of resolved aliases, but then remove anyone that doesn't have any results,
		// as the pre-determined sort order for them will be meaningless
		$aliases = $sort_aliases = collection($pool->games)->combine('home_pool_team.alias', 'home_pool_team.team_id')->toArray() + collection($pool->games)->combine('away_pool_team.alias', 'away_pool_team.team_id')->toArray();
		$pool_teams = [];
		foreach ($sort_aliases as $alias => $team_id) {
			if (empty($division->teams[$team_id]->_results->pools[$stage_id][$pool_id])) {
				$sort_aliases[$alias] = null;
			} else {
				$pool_teams[] = $division->teams[$team_id];
			}
		}
		$sort_context = ['results' => 'pool', 'stage' => $stage_id, 'pool' => $pool_id];
		\App\Lib\context_usort($pool_teams, [Comparison::class, 'compareTeamsTournamentResults'], $sort_context);
		Comparison::detectAndResolveTies($pool_teams, [Comparison::class, 'compareTeamsTournamentResults'], $sort_context);
		$seeds = array_flip(collection($pool_teams)->extract('id')->toArray());

		$pool_aliases = collection($pool->games)->combine('home_pool_team.alias', 'home_pool_team')->toArray() + collection($pool->games)->combine('away_pool_team.alias', 'away_pool_team')->toArray();
		uksort($pool_aliases, function ($a, $b) use ($sort_aliases, $seeds) {
			$team_a = $sort_aliases[$a];
			$team_b = $sort_aliases[$b];

			// If aliases haven't been resolved, sort by alias
			if (!$team_a || !$team_b) {
				preg_match('/\d+/', $a, $matches);
				$seed_a = $matches[0];
				preg_match('/\d+/', $b, $matches);
				$seed_b = $matches[0];
			} else {
				$seed_a = $seeds[$team_a];
				$seed_b = $seeds[$team_b];
			}

			if ($seed_a < $seed_b) {
				return -1;
			} else if ($seed_a > $seed_b) {
				return 1;
			}

			return 0;
		});

		$pool_dates = array_unique(collection($pool->games)->extract('game_slot.game_date')->toList());
		sort($pool_dates);
		if ($pool_dates[0] === null) {
			$pool_dates[0] = '';
			if (count($pool_dates) == 1) {
				$date_range = 0;
			} else {
				$date_range = end($pool_dates)->diffInDays($pool_dates[1]);
			}
		} else {
			$date_range = end($pool_dates)->diffInDays($pool_dates[0]);
		}
		$pool_times = $time_games = [];
		$max_games = 0;
		foreach ($pool_dates as $date_key => $date) {
			if ($date == '') {
				// Assumption here is that any games without dates also have no times
				$pool_times[$date_key] = [''];
			} else {
				$pool_times[$date_key] = array_unique(collection($pool->games)->filter(function ($game) use ($date) {
					return $game->has('game_slot') && $game->game_slot->game_date == $date;
				})->extract('game_slot.game_start')->toList());
				sort($pool_times[$date_key]);
			}

			foreach ($pool_times[$date_key] as $time_key => $time) {
				if (empty($time)) {
					// Assumption here is that the only games without times are ones with copy dependencies
					$matched_games = collection($pool->games)->match(['home_dependency_type' => 'copy']);
				} else {
					$matched_games = collection($pool->games)->filter(function ($game) use ($date, $time) {
						return $game->has('game_slot') && $game->game_slot->start_time == DateTimeCombinator::combine($date, $time);
					});
				}

				if (!$can_edit) {
					$matched_games = $matched_games->match(['published' => true]);
				}

				$time_games[$date_key][$time_key] = $matched_games->toArray();
				$max_games = max($max_games, count($time_games[$date_key][$time_key]));
			}
		}
		$cols = 2 + $max_games * 2;
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
		foreach ($pool_aliases as $alias => $pool_details):
?>
				<tr>
					<td colspan="<?= $cols ?>"><?php
						if ($aliases[$alias] !== null) {
							$team = $division->teams[$aliases[$alias]];
							echo '(';
							if (!empty($team->_results->pools[$stage_id][$pool_id]->games)) {
								$results = $team->_results->pools[$stage_id][$pool_id];
								echo $results->wins . '-' . $results->losses;
								if ($results->ties) {
									echo '-' . $results->ties;
								}
							} else {
								echo '0-0';
							}
							echo ') ' . $alias . ' ' . $this->element('Teams/block', ['team' => $team, 'show_shirt' => false]);
							if ($stage_id == 1) {
								echo __(' ({0})', $team->initial_seed);
							}
						} else {
							$dependency = $pool_details->dependency();
							echo "(0-0) $alias [$dependency]";
						}
					?></td>
				</tr>
<?php
		endforeach;

		if ($max_games > 0):
?>
				<tr>
					<td><?= __('Day') ?></td>
					<td><?= __('Time') ?></td>
<?php
					for ($i = 0; $i < $max_games; ++ $i):
?>
					<td><?= __('Game') ?></td>
					<td><?= __('Score') ?></td>
<?php
					endfor;
?>
				</tr>
<?php
		endif;

		$last_date = null;
		foreach ($pool_dates as $date_key => $date):
			foreach ($pool_times[$date_key] as $time_key => $time):
				if (!empty($time_games[$date_key][$time_key])):
?>
				<tr>
					<td><?php
						if ($last_date != $date) {
							if ($date_range < 7) {
								echo $date->i18nFormat('EEE');
							} else {
								echo $date->i18nFormat('MMM d');
							}
							$last_date = $date;
						}
					?></td>
					<td><?= !empty($time) ? $this->Time->time($time) : '' ?></td>
<?php
					foreach ($time_games[$date_key][$time_key] as $game):
						if ($game->published) {
							$class = '';
						} else if ($can_edit) {
							$class = ' class="unpublished"';
						}
?>
					<td<?= $class ?>><?= $this->Html->link($game->home_pool_team->alias . __x('standings', 'v') . $game->away_pool_team->alias, ['controller' => 'Games', 'action' => 'view', 'game' => $game->id]) ?></td>
					<td<?= $class ?>><?php
						if ($game->isFinalized()) {
							echo $game->home_score . '-' . $game->away_score . ' ' . __x('standings', '(F)');
						} else {
							$entry = $game->getBestScoreEntry();
							if ($entry) {
								if ($entry->team_id == $game->away_team) {
									echo $entry->score_against . '-' . $entry->score_for;
								} else {
									echo $entry->score_for . '-' . $entry->score_against;
								}
							}
						}
					?></td>
<?php
					endforeach;

					for ($i = count($time_games[$date_key][$time_key]); $i < $max_games; ++ $i):
?>
					<td></td>
					<td></td>
<?php
					endfor;
?>
				</tr>
<?php
				endif;
			endforeach;
		endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<?php
	endforeach;
?>
<div class="clear-float"></div>
<?php
endforeach;
