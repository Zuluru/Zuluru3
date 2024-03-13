<?php
/**
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 * @var \App\Model\Entity\TeamEvent $event
 * @var \Cake\I18n\FrozenDate $date
 * @var bool $is_me
 * @var bool $is_captain
 * @var mixed[] $attendance_options
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Team Events'));
$this->Html->addCrumb(__('Attendance Change'));
$this->Html->addCrumb($team->name);
?>

<div class="team_events form">
<h2><?= __('Attendance Change') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('Team') ?></dt>
		<dd><?= $this->element('Teams/block', ['team' => $team]) ?></dd>
		<dt><?= __('Event') ?></dt>
		<dd><?= $event->name ?></dd>
		<dt><?= __('Description') ?></dt>
		<dd><?= $event->description ?>&nbsp;</dd>
		<dt><?= __('Date') ?></dt>
		<dd><?= $this->Time->date($event->date) ?></dd>
		<dt><?= __('Start Time') ?></dt>
		<dd><?= $this->Time->time($event->start) ?></dd>
		<dt><?= __('End Time') ?></dt>
		<dd><?= $this->Time->time($event->end) ?></dd>
	</dl>

<?php
$status_descriptions = Configure::read('attendance');
$roster_descriptions = Configure::read('options.roster_role');
if (!$is_me) {
	echo $this->Html->para(null, __('You are attempting to change attendance for {0} ({1}).',
		$this->element('People/block', ['person' => $attendance->person]),
		$roster_descriptions[$attendance->person->teams[0]->_joinData->role]
	));
}
echo $this->Html->para(null, __('Current status: {0}',
	$this->Html->tag('strong', __($status_descriptions[$attendance->status]))
));

echo $this->Html->para(null, __('Possible attendance options are:'));
echo $this->Form->create($attendance, ['align' => 'horizontal']);
echo $this->Form->input('status', [
	'label' => false,
	'type' => 'radio',
	'options' => $attendance_options,
	'default' => $attendance->status,
]);
echo $this->Form->input('comment', [
	'label' => __('You may optionally add a comment'),
	'size' => 80,
	'default' => $attendance->comment,
]);
if ($is_captain && array_key_exists(ATTENDANCE_INVITED, $attendance_options)) {
	echo $this->Form->input('note', [
		'label' => __('You may optionally add a personal note which will be included in the invitation email to the player'),
		'size' => 80,
	]);
}

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
