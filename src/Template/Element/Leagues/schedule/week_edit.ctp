<?php

use App\Model\Table\GameSlotsTable;

/**
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
 * @type $multi_day boolean
 * @type $edit_date \Cake\I18n\FrozenDate
 * @type $week \Cake\I18n\FrozenDate[]
 */

use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\I18n\Number;
use Cake\Utility\Text;

if (isset($division)) {
	$games = $division->games;
	$competition = ($division->schedule_type === 'competition');
	$double_booking = $division->double_booking;
	$id = $division->id;
	$id_field = 'division';
	$teams = collection($division->teams)->combine('id', function (Team $team) { return Text::truncate($team->name, 16); })->toArray();
	natcasesort($teams);
	$only_some_divisions = false;
} else {
	$games = $league->games;
	$competition = collection($league->divisions)->every(function ($division) { return $division->schedule_type === 'competition'; });
	$double_booking = collection($league->divisions)->some(function ($division) { return $division->double_booking; });
	$id = $league->id;
	$id_field = 'league';

	$teams = [];
	foreach ($league->divisions as $league_division) {
		if ($this->Authorize->can('edit', $league_division)) {
			$name = $league_division->translateField('name');
			$teams[$name] = collection($league_division->teams)->combine('id', function (Team $team) { return Text::truncate($team->name, 16); })->toArray();
			if (empty($teams[$name])) {
				unset($teams[$name]);
			} else {
				natcasesort($teams[$name]);
			}
		}
	}
	$only_some_divisions = (count($league->divisions) !== count($teams));
	if (count($teams) === 1) {
		$teams = reset($teams);
	}
}

// Put the slots into a more useful form for us
$slots = [];
usort($game_slots, [GameSlotsTable::class, 'compareTimeAndField']);
foreach ($game_slots as $slot) {
	if ($is_tournament || $multi_day) {
		$slots[$slot->id] = $this->Time->day($slot->game_date) . ' ' . $this->Time->time($slot->game_start) . ' ' . $slot->field->long_name;
	} else {
		$slots[$slot->id] = $this->Time->time($slot->game_start) . ' ' . $slot->field->long_name;
	}
}

$published = collection($games)->filter(function ($game) use ($week) {
	return $game->game_slot->game_date->between($week[0], $week[1]);
})->every(function ($game) {
	return $game->published;
});

$dependency_types = [
	'game_winner' => __('Winner of'),
	'game_loser' => __('Loser of'),
];

// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
$finalized = true;
$is_season = $is_tournament = $editing_tournament = $has_dependent_games = false;
$season_divisions = [];
foreach ($games as $game) {
	if ($game->game_slot->game_date->between($week[0], $week[1])) {
		$can_edit = $this->Authorize->can('edit', $game);
		if ($game->type !== SEASON_GAME) {
			$is_tournament = true;
			if ($can_edit) {
				$editing_tournament = true;
			}
		} else {
			$is_season = true;
			$season_divisions[$game->division_id] = true;
		}
		if ($can_edit) {
			$finalized &= $game->isFinalized();
			$has_dependent_games |= (!empty($game->home_pool_team->dependency_type) || !empty($game->away_pool_team->dependency_type));
		}
	}
}

$cross_division = (count($season_divisions) > 1);

if ($only_some_divisions || !$is_season) {
	echo $this->element('Leagues/schedule/view_header', compact('league', 'week', 'competition', 'id_field', 'id', 'published', 'finalized', 'is_tournament', 'multi_day', 'has_dependent_games'));
} else {
	echo $this->element('Leagues/schedule/edit_header', compact('league', 'week', 'competition', 'id_field', 'id', 'is_tournament', 'multi_day'));
}
?>

<?php
if ($editing_tournament):
?>
<tr><td colspan="<?= 5 + $multi_day + !$competition ?>" class="warning-message"><?= __('For normal usage, it is safest to only change {0} values for tournament or playoff games; editing of other values should be reserved for extreme situations.', __('Time/{0}', __(Configure::read("sports.{$league->sport}.field_cap")))) ?></td></tr>
<?php
endif;

if (isset($division)) {
	echo $this->Form->create($division, ['align' => 'horizontal']);
} else {
	echo $this->Form->create($league, ['align' => 'horizontal']);
}

$last_date = $last_slot = null;
foreach ($games as $game):
	if (!$game->game_slot->game_date->between($week[0], $week[1])) {
		continue;
	}

	$same_date = ($game->game_slot->game_date === $last_date);
	$same_slot = ($game->game_slot->id === $last_slot);
	if (!$this->Authorize->can('edit', $game)) {
		if ($game->published) {
			echo $this->element('Leagues/schedule/game_view', compact('game', 'competition', 'is_tournament', 'multi_day', 'same_date', 'same_slot'));
			$last_date = $game->game_slot->game_date;
			$last_slot = $game->game_slot->id;
		}
		continue;
	}
	$last_date = $game->game_slot->game_date;
	$last_slot = $game->game_slot->id;
?>

<tr<?= (!$game->published) ? ' class="unpublished"' : '' ?>>
	<td><?php
		echo $this->Form->hidden("games.{$game->id}.id", ['value' => $game->id]);
		echo $this->Form->hidden("games.{$game->id}.type", ['value' => $game->type]);
		if ($game->type !== SEASON_GAME) {
			if ($game->placement) {
				echo $this->Form->hidden("games.{$game->id}.placement", ['value' => $game->placement]);
				echo Number::ordinal($game->placement);
			} else {
				echo $this->Form->input("games.{$game->id}.name", [
					'div' => false,
					'label' => false,
					'size' => 5,
				]);
			}
		}
	?></td>
	<td colspan="<?= 2 + $multi_day ?>"><?php
		echo $this->Form->input("games.{$game->id}.game_slot_id", [
			'div' => false,
			'label' => false,
			'options' => $slots,
			'empty' => '---',
		]);
	?></td>
	<td><?php
		if ($game->type !== SEASON_GAME) {
			$ids = [];

			if ($game->home_dependency_type === 'pool') {
				// Get the list of seeds in the pool
				foreach ($games as $other_game) {
					if ($other_game->division_id === $game->division_id && $other_game->type !== SEASON_GAME && $other_game->round === $game->round && $other_game->pool_id === $game->pool_id) {
						if (!empty($other_game->home_pool_team) && !in_array($other_game->home_pool_team->id, $ids)) {
							$dependency = $other_game->home_pool_team->dependency();
							$alias = $other_game->home_pool_team->alias;
							if (!empty($alias)) {
								$dependency = "$alias [$dependency]";
							}
							$ids[$other_game->home_pool_team->id] = $dependency;
						}

						if (!empty($other_game->away_pool_team) && !in_array($other_game->away_pool_team->id, $ids)) {
							$dependency = $other_game->away_pool_team->dependency();
							$alias = $other_game->away_pool_team->alias;
							if (!empty($alias)) {
								$dependency = "$alias [$dependency]";
							}
							$ids[$other_game->away_pool_team->id] = $dependency;
						}
					}
				}

				echo $this->Form->input("games.{$game->id}.home_pool_team_id", [
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
				]);
			} else {
				// Get the list of games in earlier rounds
				foreach ($games as $other_game) {
					if ($other_game->division_id === $game->division_id && $other_game->type !== SEASON_GAME && $other_game->type !== POOL_PLAY_GAME && $other_game->round < $game->round) {
						$ids[$other_game->id] = $other_game->display_name;
					}
				}
				ksort($ids);

				// TODOBOOTSTRAP Can these two be smaller, so they fit side-by-side better?
				echo $this->Form->input("games.{$game->id}.home_dependency_type", [
					'div' => false,
					'label' => false,
					'options' => $dependency_types,
					'empty' => '---',
				]);
				echo $this->Form->input("games.{$game->id}.home_dependency_id", [
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
				]);
			}
		} else {
			echo $this->Form->input("games.{$game->id}.home_team_id", [
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
			]);
		}
	?></td>
<?php
	if (!$competition):
?>
	<td><?php
		if ($game->type !== SEASON_GAME) {
			$ids = [];

			if ($game->away_dependency_type === 'pool') {
				// Get the list of seeds in the pool
				foreach ($games as $other_game) {
					if ($other_game->division_id === $game->division_id && $other_game->type !== SEASON_GAME && $other_game->round === $game->round && $other_game->pool_id === $game->pool_id) {
						if (!in_array($other_game->home_pool_team->id, $ids)) {
							$dependency = $other_game->home_pool_team->dependency();
							$alias = $other_game->home_pool_team->alias;
							if (!empty($alias)) {
								$dependency = "$alias [$dependency]";
							}
							$ids[$other_game->home_pool_team->id] = $dependency;
						}

						if (!in_array($other_game->away_pool_team->id, $ids)) {
							$dependency = $other_game->away_pool_team->dependency();
							$alias = $other_game->away_pool_team->alias;
							if (!empty($alias)) {
								$dependency = "$alias [$dependency]";
							}
							$ids[$other_game->away_pool_team->id] = $dependency;
						}
					}
				}

				echo $this->Form->input("games.{$game->id}.away_pool_team_id", [
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
				]);
			} else {
				// Get the list of games in earlier rounds
				foreach ($games as $other_game) {
					if ($other_game->division_id === $game->division_id && $other_game->type !== SEASON_GAME && $other_game->type !== POOL_PLAY_GAME && $other_game->round < $game->round) {
						$ids[$other_game->id] = $other_game->display_name;
					}
				}
				ksort($ids);

				// TODOBOOTSTRAP These two too...
				echo $this->Form->input("games.{$game->id}.away_dependency_type", [
					'div' => false,
					'label' => false,
					'options' => $dependency_types,
					'empty' => '---',
				]);
				echo $this->Form->input("games.{$game->id}.away_dependency_id", [
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
				]);
			}
		} else {
			echo $this->Form->input("games.{$game->id}.away_team_id", [
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
			]);
		}
	?></td>
<?php
	endif;
?>
	<td></td>
</tr>

<?php
endforeach;
?>

<tr>
	<td colspan="<?= 3 + $multi_day + !$competition ?>"><?php
		// TODOBOOTSTRAP: This is creating a wide (though transparent) div that partially "covers up" the Submit button so that only the bottom of it is clickable
		echo $this->Form->input('options.publish', [
			'label' => __('Set as published for player viewing?'),
			'type' => 'checkbox',
			'checked' => $published,
		]);
		if ($is_season) {
			echo $this->Form->input('options.double_header', [
				'label' => __('Allow double-headers?'),
				'type' => 'checkbox',
			]);
		}
		if ($multi_day) {
			echo $this->Form->input('options.multiple_days', [
				'label' => __('Allow teams to be booked on more than one day?'),
				'type' => 'checkbox',
			]);
		}
		if ($double_booking) {
			echo $this->Form->input('options.double_booking', [
				'label' => __('Allow double-booking?'),
				'type' => 'checkbox',
			]);
		}
		if ($cross_division) {
			echo $this->Form->input('options.cross_division', [
				'label' => __('Allow cross-division games?'),
				'type' => 'checkbox',
			]);
		}
	?></td>
	<td colspan="2" class="actions splash-action">
		<?= $this->Form->button(__('Submit'), ['class' => 'btn-success', ]) ?>
		<?= $this->Form->button(__('Reset'), ['type' => 'reset']) ?>
	</td>
</tr>

<?php
echo $this->Form->end();
