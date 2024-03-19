<?php
/**
 * @var \App\Model\Entity\Division $division
 * @var bool $multi_day
 * @var \Cake\I18n\FrozenDate $edit_date
 * @var \App\Model\Entity\GameSlot[] $game_slots
 * @var bool $is_tournament
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Schedule'));

if (!empty($division->header)):
?>
<div class="division_header"><?= $division->header ?></div>
<?php
endif;
?>

	<div class="divisions schedule">
		<h2><?= __('Division Schedule') . ': ' . $division->full_league_name ?></h2>
<?php
if ($division->schedule_type === 'tournament') {
	echo $this->element('Leagues/schedule/tournament/notice');
}

if (!empty($division->games)):
	$future_week = 99;
	$dates = collection($division->games);
	if (!$this->Authorize->can('edit_schedule', $division)) {
		$dates = $dates->filter(function ($game) { return $game->published; });
	}
	$dates = array_unique($dates->extract('game_slot.game_date')->toArray());
	$weeks = [];
	$week = 0;
	$first_day = Configure::read('organization.first_day');
	foreach ($dates as $date) {
		if ($is_tournament) {
			++ $week;
		} else {
			$week = $date->format('W');
			if ($date->format('N') >= $first_day) {
				++ $week;
			}
		}
		if (!array_key_exists($week, $weeks)) {
			$weeks[$week] = [$date, $date];
		} else {
			$weeks[$week][0] = min($date, $weeks[$week][0]);
			$weeks[$week][1] = max($date, $weeks[$week][1]);
		}

		if ($date->isFuture() && $future_week == 99) {
			$future_week = $week;
		}
	}

	if ($future_week != 99) {
		echo $this->Html->para(null, $this->Html->link(__('Jump to upcoming games'), "#{$weeks[$future_week][0]}"));
	}
?>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
<?php
	$competition = ($division->schedule_type === 'competition');
?>
					<tr>
						<th><?= $is_tournament ? __('Game') : '' ?></th>
<?php
	if ($multi_day):
?>
						<th><?= __('Date') ?></th>
<?php
	endif;
?>
						<th><?= __('Time') ?></th>
						<th><?= __(Configure::read("sports.{$division->league->sport}.field_cap")) ?></th>
						<th><?= $competition ? __('Team') : __('Home') ?></th>
<?php
	if (!$competition):
?>
						<th><?= __('Away') ?></th>
<?php
	endif;
?>
						<th><?= __('Score') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($weeks as $week) {
		if ($edit_date >= $week[0] && $edit_date <= $week[1]) {
			echo $this->element('Leagues/schedule/week_edit', array_merge(['league' => $division->league], compact('week', 'multi_day', 'game_slots', 'is_tournament')));
		} else {
			echo $this->element('Leagues/schedule/week_view', array_merge(['league' => $division->league], compact('week', 'multi_day')));
		}
	}
?>

				</tbody>
			</table>
		</div>
<?php
endif;
?>

	</div>

	<div class="actions columns"><?= $this->element('Divisions/actions', [
		'league' => $division->league,
		'division' => $division,
		'format' => 'list',
	]) ?></div>
<?php
if (!empty($division->footer)):
?>
	<div class="clear-float division_footer"><?= $division->footer ?></div>
<?php
endif;
