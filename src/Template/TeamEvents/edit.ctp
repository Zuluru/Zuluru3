<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Team Events'));
if ($team_event->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($team_event->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="team_events form">
	<?= $this->Form->create($team_event, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $team_event->isNew() ? __('Create Team Event') : __('Edit Team Event') ?></legend>
<?php
echo $this->Form->input('name', ['label' => __('Event Name')]);
echo $this->Form->hidden('team_id');
echo $this->Form->input('description');
if (Configure::read('feature.urls')) {
	echo $this->Form->input('website');
}
echo $this->Form->input('date');
echo $this->Form->input('start');
echo $this->Form->input('end');

if ($team_event->isNew()):
	echo $this->Jquery->toggleInput('repeat', [
		'type' => 'checkbox',
		'label' => __('This is a repeating event'),
	], [
		'selector' => '#RepeatDetails',
	]);
?>
		<fieldset id="RepeatDetails">
			<legend><?= __('Event Repetition Details') ?></legend>
<?php
	echo $this->Form->input('repeat_count', [
		'label' => __('Number of events to create'),
		'size' => 6,
	]);
	$this->Form->unlockField('repeat_count');
	echo $this->Form->input('repeat_type', [
		'label' => __('Create events'),
		'options' => [
			'weekly' => __('Once a week on the same day'),
			'daily' => __('Every day'),
			'weekdays' => __('Every weekday'),
			'weekends' => __('Every Saturday and Sunday'),
			'custom' => __('On days that I will specify'),
		],
	]);
	$this->Form->unlockField('repeat_type');
?>
		</fieldset>
<?php
endif;

echo $this->Form->input('location_name', ['label' => __('Location')]);
echo $this->Form->input('location_street', ['label' => __('Address')]);
echo $this->Form->input('location_city', ['label' => __('City')]);
echo $this->Form->input('location_province', [
	'label' => __('Province'),
	'options' => $provinces,
	'empty' => '---',
]);
	?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if (!$team_event->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'event' => $team_event->id],
		['alt' => __('Delete'), 'title' => __('Delete Team Event')],
		['confirm' => __('Are you sure you want to delete this team_event?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Team Event')]));
}
?>
	</ul>
</div>
