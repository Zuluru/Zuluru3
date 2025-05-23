<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Attendance $attendance
 * @var \Cake\I18n\FrozenDate $game_date
 * @var string[] $attendance_options
 * @var bool $is_me
 * @var bool $is_captain
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Attendance Change'));
$this->Breadcrumbs->add(h($team->name));
?>

<div class="games form">
<h2><?= __('Attendance Change') ?></h2>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Teams/block', ['team' => $team]) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Date') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (!$game->isNew()) {
			echo $this->Time->date($game->game_slot->game_date);
		} else {
			echo $this->Time->date($game_date);
		}
		?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Time') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (!$game->isNew()) {
			echo $this->Time->time($game->game_slot->game_start);
		} else {
			echo __('TBD');
		}
		?></dd>
		<dt class="col-sm-3 text-end"><?= __('Opponent') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (isset($opponent)) {
			echo $this->element('Teams/block', ['team' => $opponent]);
		} else {
			echo __('TBD');
		}
		?></dd>
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
echo $this->Form->control('status', [
	'label' => false,
	'type' => 'radio',
	'options' => $attendance_options,
	'default' => $attendance->status,
]);
echo $this->Form->control('comment', [
	'label' => __('You may optionally add a comment'),
	'size' => 80,
	'default' => $attendance->comment,
]);
if ($is_captain && array_key_exists(ATTENDANCE_INVITED, $attendance_options)) {
	echo $this->Form->control('note', [
		'label' => __('You may optionally add a personal note which will be included in the invitation email to the player'),
		'size' => 80,
	]);
}

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
