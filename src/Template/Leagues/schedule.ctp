<?php
/**
 * @type \App\Model\Entity\League $league
 * @type boolean $multi_day
 * @type \Cake\I18n\FrozenDate $edit_date
 */

use App\Model\Entity\Division;
use Cake\Core\Configure;

$tournaments = collection($league->divisions)->every(function (Division $division) {
	return $division->schedule_type == 'tournament';
});
$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
$this->Html->addCrumb($league->full_name);
$this->Html->addCrumb(__('Schedule'));
?>

<?php
$collapse = (count($league->divisions) == 1);

if ($collapse):
	$header = $league->divisions[0]->translateField('header');
	if (!empty($header)):
?>
<div class="division_header"><?= $header ?></div>
<?php
	endif;
endif;
?>
<div class="leagues schedule">
<h2><?= ($tournaments ? __('Tournament Schedule') : __('League Schedule')) . ': ' . $league->full_name ?></h2>
<?php
if (collection($league->divisions)->some(function ($division) { return $division->schedule_type == 'tournament'; })) {
	echo $this->element('Leagues/schedule/tournament/notice');
}

if (!empty($league->games)):
	$future_week = 99;
	$dates = collection($league->games);
	if (!$this->Authorize->can('edit_schedule', $league)) {
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
	$competition = collection($league->divisions)->every(function ($division) { return $division->schedule_type == 'competition'; });
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
						<th><?= __(Configure::read("sports.{$league->sport}.field_cap")) ?></th>
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
			echo $this->element('Leagues/schedule/week_edit', compact('league', 'week', 'multi_day', 'slots', 'is_tournament'));
		} else {
			echo $this->element('Leagues/schedule/week_view', compact('league', 'week', 'multi_day'));
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

	<div class="actions columns"><?= $this->element('Leagues/actions', [
		'league' => $league,
		'format' => 'list',
		'tournaments' => $tournaments,
	]) ?></div>
<?php
if ($collapse):
	$footer = $league->divisions[0]->translateField('footer');
	if (!empty($footer)):
?>
	<div class="clear-float division_footer"><?= $footer ?></div>
<?php
	endif;
endif;
