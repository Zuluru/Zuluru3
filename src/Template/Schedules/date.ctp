<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Division'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Add Games'));
$this->Html->addCrumb(__('Select Date'));
?>

<div class="schedules add">
<?= $this->element('Schedules/exclude') ?>

<?php
// If we previously had an array of start dates and now we will have just one,
// or if we previously had just one and now we will have an array, we need to
// clear the saved value, so that it doesn't get included in the hidden fields
// and cause a black hold.
if (empty($preview)) {
	if (is_array($division->_options['start_date'])) {
		unset($division->_options['start_date']);
	}
} else {
	if (!is_array($division->_options['start_date'])) {
		unset($division->_options['start_date']);
	}
}

echo $this->Form->create($division, ['align' => 'horizontal']);
$saved_step = $division->_options->step;
$division->_options->step = 'date';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

<fieldset>
<legend><?= __('Select desired start date') ?></legend>

<p><?= __('Scheduling a {0} will create a total of ', $desc);
if (count($required_field_counts) > 1) {
	// A simple array with multiple elements means that multiple time slots are required.
	$total_fields = array_sum($required_field_counts);
	$min_slots = count($required_field_counts);
	echo __('{0} games across a minimum of {1} time slots.', $total_fields, $min_slots);
} else {
	// A simple array with one element means games may happen in a single time slot.
	$total_fields = array_sum($required_field_counts);
	echo __('{0} games.', $total_fields);
}
?></p>

<?php
if (!empty($preview)):
?>
<p><?= __('This will create the following games:') . $this->Html->nestedList($preview) ?></p>
<?php
endif;

if (empty($dates)):
?>
<p><?php
	echo __('You have no future dates available.');
	if (Configure::read('feature.allow_past_games') && empty($division->_options->past)) {
		echo ' ';
		echo __('Choose "Schedule games in the past" below to see past options, or make future game slots available to this division and try again.');
	}
?></p>
<?php
else:
	// We have an array like 0 => timestamp, and need timestamp => readable
	$dates = array_combine(array_values($dates), array_values($dates));
	if (empty($preview)) {
		$dates = array_map([$this->Time, 'fulldate'], $dates);
		echo $this->Form->control('_options.start_date', [
			'options' => $dates,
		]);
	} else {
?>
<p><?php
		echo __('Choose your preferred time slot for each round.');
		echo ' ';
		echo __('This allows you to ensure that teams have a maximum number of games on each day, place byes where necessary, etc.');
		echo ' ';
		echo __('Note that games will be placed no earlier than these time slots, but may be later depending on field availability.');
		echo ' ';
		echo __('Rounds may be scheduled to start after "later" rounds, for example if you have a particular matchup that you need to schedule at a particular time.');
		echo ' ';
		echo __('If you leave all rounds at the earliest possible time, the system will schedule games as closely as possible; you don\'t need to set each round\'s time if you have no constraints.');
		echo ' ';
?></p>
<?php
		$dates = array_map([$this->Time, 'fulldatetime'], $dates);
		foreach (array_keys($preview) as $round) {
			// TODO: Seems there's a CakePHP bug where just using .$round causes the submission from the confirm page to be black-holed;
			// numeric keyed hidden inputs aren't handled correctly. When that's fixed, we can change ".round$round" to just ".$round",
			// and make the corresponding change in LeagueTypeTournament::assignFieldsByRound.
			echo $this->Form->control("_options.start_date.round$round", [
				'label' => __('Round {0}', $round),
				'options' => $dates,
			]);
		}
	}
endif;
?>

</fieldset>

<?php
if (!empty($dates)) {
	echo $this->Form->button(__('Next step'), ['class' => 'btn-success']);
	echo $this->Form->end();
}
?>

<?php
if (Configure::read('feature.allow_past_games') && empty($division->_options->past)) {
	echo $this->Form->create($division, ['align' => 'horizontal']);
	$division->_options->step = $saved_step;
	$division->_options->past = true;
	echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
	echo $this->Form->button(__('Schedule games in the past'));
	echo $this->Form->end();
}
?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
	</ul>
</div>
