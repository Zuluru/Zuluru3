<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var bool $adding
 * @var bool|string $can_add
 * @var string[] $roster_role_options
 */

use Cake\Core\Configure;

$adding_noun = $adding ? __('Addition') : __('Invitation');

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Roster {0}', $adding_noun));
$this->Breadcrumbs->add($person->full_name);
?>

<div class="people form">
	<h2><?= __('Roster {0}', $adding_noun) . ': ' . $team->name . ': ' . $person->full_name ?></h2>
<?php
if ($adding) {
	echo $this->Html->para(null, __('You are adding') . ' ' .
		$this->element('People/block', compact('person')) .
		' ' . __('to the team') . ' ' .
		$this->element('Teams/block', ['team' => $team, 'show_shirt' => false]) .
		'.');
} else {
	echo $this->Html->para(null, __('You are inviting') . ' ' .
		$this->element('People/block', compact('person')) .
		' ' . __('to join the team') . ' ' .
		$this->element('Teams/block', ['team' => $team, 'show_shirt' => false]) .
		'. ' .
		__('They will have to accept your invitation before they are considered an active member of the team.'));
}
if ($can_add !== true) {
	echo $this->Html->para('warning-message', $this->Html->formatMessage($can_add) . ' ' .
		__('They can still be invited to join, but will not be allowed to accept the invitation or play with your team until this is resolved.'));
}

echo $this->Form->create($team, ['align' => 'horizontal']);

echo $this->Html->para(null, __('Possible roster roles are:'));
echo $this->Form->control('role', [
	'label' => false,
	'type' => 'radio',
	'options' => $roster_role_options,
	'default' => 'player',
]);

$positions = $team->division_id ? Configure::read("sports.{$team->division->league->sport}.positions") : [];
if (!empty($positions)) {
	echo $this->Html->para(null, __('Possible roster positions are:'));
	echo $this->Form->control('position', [
		'label' => false,
		'type' => 'radio',
		'options' => $positions,
		'default' => 'unspecified',
	]);
} else {
	echo $this->Form->hidden('position', ['value' => 'unspecified']);
}

// TODO: If the team has numbers, add a field for entering that here

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
