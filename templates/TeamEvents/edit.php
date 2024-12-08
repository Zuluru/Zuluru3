<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TeamEvent $team_event
 * @var string[] $provinces
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Team Events'));
if ($team_event->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($team_event->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="team_events form">
	<?= $this->Form->create($team_event, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $team_event->isNew() ? __('Create Team Event') : __('Edit Team Event') ?></legend>
<?php
echo $this->Form->control('name', ['label' => __('Event Name')]);
echo $this->Form->hidden('team_id');
echo $this->Form->control('description');
if (Configure::read('feature.urls')) {
	echo $this->Form->control('website');
}
echo $this->Form->control('date');
echo $this->Form->control('start');
echo $this->Form->control('end');

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
	echo $this->Form->control('repeat_count', [
		'label' => __('Number of events to create'),
		'size' => 6,
	]);
	if ($this->Form->hasFormProtector()) {
		$this->Form->unlockField('repeat_count');
	}
	echo $this->Form->control('repeat_type', [
		'label' => __('Create events'),
		'options' => [
			'weekly' => __('Once a week on the same day'),
			'daily' => __('Every day'),
			'weekdays' => __('Every weekday'),
			'weekends' => __('Every Saturday and Sunday'),
			'custom' => __('On days that I will specify'),
		],
	]);
	if ($this->Form->hasFormProtector()) {
		$this->Form->unlockField('repeat_type');
	}
?>
		</fieldset>
<?php
endif;

echo $this->Form->control('location_name', ['label' => __('Location')]);
echo $this->Form->control('location_street', ['label' => __('Address')]);
echo $this->Form->control('location_city', ['label' => __('City')]);
echo $this->Form->control('location_province', [
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
<?php
if (!$team_event->isNew()) {
	echo $this->Bootstrap->navPills([
		$this->Form->iconPostLink('delete_32.png',
			['action' => 'delete', '?' => ['event' => $team_event->id]],
			['alt' => __('Delete'), 'title' => __('Delete Team Event')],
			['confirm' => __('Are you sure you want to delete this team_event?')]
		),
		$this->Html->iconLink('add_32.png',
			['action' => 'add'],
			['alt' => __('Add'), 'title' => __('Add Team Event')]
		),
	]);
}
?>
</div>
