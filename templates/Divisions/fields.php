<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('{0} Distribution Report', __(Configure::read("sports.{$division->league->sport}.field_cap"))));
?>

<div class="divisions field_distribution">
	<h2><?= __('{0} Distribution Report', __(Configure::read("sports.{$division->league->sport}.field_cap"))) . ': ' . $division->full_league_name ?></h2>
<?php
if (isset($published)) {
	echo $this->Html->para(null,
		__('This report includes only games that are published. You may also see it {0}.',
			$this->Html->link(__('including all games'), ['action' => 'fields', '?' => ['division' => $division->id]]))
	);
} else {
	echo $this->Html->para(null,
		__('This report includes all games. You may also see it {0}.',
			$this->Html->link(__('including only games that are published'), ['action' => 'fields', '?' => ['division' => $division->id, 'published' => true]]))
	);
}

$regions = count(array_unique(collection($game_slots)->extract('field.facility.region_id')->toArray()));
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
<?php // TODO: Use a league element ?>
				<tr>
					<th<?= $regions > 1 ? ' rowspan="2"' : ''?>><?= __('Team') ?></th>
					<th<?= $regions > 1 ? ' rowspan="2"' : ''?>><?= __('Rating') ?></th>
<?php
$region_prefs = Configure::read('feature.region_preference');
if ($region_prefs):
?>
					<th<?= $regions > 1 ? ' rowspan="2"' : ''?>><?= __('Region Preference') ?></th>

<?php
endif;

$count = 0;
$last_region = null;
$heading = [];
foreach ($game_slots as $game_slot) {
	if ($last_region == $game_slot->field->facility->region->name) {
		++ $count;
	} else {
		if ($count) {
			if ($regions > 1) {
				$heading[] = __('Sub total');
				++ $count;
				echo $this->Html->tag('th', __($last_region), ['colspan' => $count]);
			}
		}
		$last_region = $game_slot->field->facility->region->name;
		$count = 1;
	}
	$th = $this->Html->link($game_slot->field->facility->code,
		['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game_slot->field->facility->id]],
		['title' => $game_slot->field->facility->name]
	) . ' ' . $this->Time->time($game_slot->game_start);
	$heading[] = $th;
	if ($regions == 1) {
		echo $this->Html->tag('th', $th);
	}
}
if ($count) {
	if ($regions > 1) {
		$heading[] = __('Sub total');
		++ $count;
		echo $this->Html->tag('th', __($last_region), ['colspan' => $count]);
	}
}
?>
					<th<?= $regions > 1 ? ' rowspan="2"' : ''?>><?= __('Total') ?></th>
				</tr>

<?php
if ($regions > 1) {
	echo $this->Html->tableHeaders($heading);
}
?>
			</thead>
			<tbody>

<?php
// Count number of games per facility for each team
$team_count = [];
$facility_count = [];
$teams = ['home_team_id'];
if ($division->schedule_type != 'competition') {
	$teams[] = 'away_team_id';
}

foreach ($division->games as $game) {
	$game_start = $this->Time->time($game->game_slot->game_start);
	foreach ($teams as $team) {
		if (!array_key_exists($game->$team, $team_count)) {
			$team_count[$game->$team] = [];
		}
		if (!array_key_exists($game->game_slot->field->facility->code, $team_count[$game->$team])) {
			$team_count[$game->$team][$game->game_slot->field->facility->code] = [];
		}
		if (!array_key_exists($game_start, $team_count[$game->$team][$game->game_slot->field->facility->code])) {
			$team_count[$game->$team][$game->game_slot->field->facility->code][$game_start] = 0;
		}
		++ $team_count[$game->$team][$game->game_slot->field->facility->code][$game_start];
	}
	if (!array_key_exists($game->game_slot->field->facility->code, $facility_count)) {
		$facility_count[$game->game_slot->field->facility->code] = [];
	}
	if (!array_key_exists($game_start, $facility_count[$game->game_slot->field->facility->code])) {
		$facility_count[$game->game_slot->field->facility->code][$game_start] = 0;
	}
	++ $facility_count[$game->game_slot->field->facility->code][$game_start];
}
$numteams = count($team_count);

$rows = [];
foreach ($division->teams as $team) {
	$id = $team->id;
	$row = [$this->element('Teams/block', ['team' => $team, 'show_shirt' => false]), $team->rating];
	if ($region_prefs) {
		if (!empty($team->region)) {
			$row[] = $team->region->name;
		} else {
			$row[] = '';
		}
	}

	$last_region = null;
	$total = 0;
	foreach ($game_slots as $game_slot) {
		if ($regions > 1 && $last_region != $game_slot->field->facility->region->name) {
			if ($last_region !== null) {
				$row[] = [$region_total, ['class' => 'sub-total']];
			}
			$region_total = 0;
			$last_region = $game_slot->field->facility->region->name;
		}

		$game_start = $this->Time->time($game_slot->game_start);
		if (array_key_exists($id, $team_count) &&
			array_key_exists($game_slot->field->facility->code, $team_count[$id]) &&
			array_key_exists($game_start, $team_count[$id][$game_slot->field->facility->code]))
		{
			$games = $team_count[$id][$game_slot->field->facility->code][$game_start];
		} else {
			$games = 0;
		}
		$total += $games;
		if ($regions > 1) {
			$region_total += $games;
		}

		if (array_key_exists($game_slot->field->facility->code, $facility_count) &&
			array_key_exists($game_start, $facility_count[$game_slot->field->facility->code]))
		{
			$avg = $facility_count[$game_slot->field->facility->code][$game_start] / $numteams;
			if ($division->schedule_type != 'competition') {
				$avg *= 2;
			}
		} else {
			$avg = 0;
		}

		if (abs($avg - $games) > 1.5) {
			$row[] = [$games, ['class' => 'field-usage-highlight']];
		} else {
			$row[] = $games;
		}
	}

	if ($regions > 1) {
		$row[] = [$region_total, ['class' => 'sub-total']];
	}

	$row[] = $total;
	$rows[] = $row;
}

// Output totals line
$total_row = [[__('Total games'), ['colspan' => 2 + $region_prefs]]];
$avg_row = [[__('Average'), ['colspan' => 2 + $region_prefs]]];
$region_total = 0;
$last_region = null;
foreach ($game_slots as $game_slot) {
	if ($regions > 1 && $last_region != $game_slot->field->facility->region->name) {
		if ($last_region !== null) {
			$total_row[] = [$region_total, ['class' => 'sub-total']];
			$avg = $region_total / $numteams;
			if ($division->schedule_type != 'competition') {
				$avg *= 2;	// Each game has 2 teams participating
			}
			$avg_row[] = [sprintf('%0.1f', $avg), ['class' => 'sub-total']];
		}
		$region_total = 0;
		$last_region = $game_slot->field->facility->region->name;
	}

	$game_start = $this->Time->time($game_slot->game_start);
	if (array_key_exists($game_slot->field->facility->code, $facility_count) &&
		array_key_exists($game_start, $facility_count[$game_slot->field->facility->code]))
	{
		$total = $facility_count[$game_slot->field->facility->code][$game_start];
	} else {
		$total = 0;
	}
	$region_total += $total;
	$total_row[] = $total;
	$avg = $total / $numteams;
	if ($division->schedule_type != 'competition') {
		$avg *= 2;
	}
	$avg_row[] = sprintf('%0.1f', $avg);
}

if ($regions > 1) {
	$total_row[] = [$region_total, ['class' => 'sub-total']];
	$avg = $region_total / $numteams;
	if ($division->schedule_type != 'competition') {
		$avg *= 2;
	}
	$avg_row[] = [sprintf('%0.1f', $avg), ['class' => 'sub-total']];
}

$total = $total_row[] = array_sum($total_row);
$avg = $total / $numteams;
if ($division->schedule_type != 'competition') {
	$avg *= 2;
}
$avg_row[] = sprintf('%0.1f', $avg);
$rows[] = $total_row;
$rows[] = $avg_row;

echo $this->Html->tableCells($rows);
?>
			</tbody>
<?php
if ($region_prefs) {
	array_unshift($heading, __('Region Preference'));
}
array_unshift($heading, __('Rating'));
array_unshift($heading, __('Team'));
$heading[] = __('Total');

echo $this->Html->tableHeaders($heading);
?>
		</table>
	</div>
</div>
