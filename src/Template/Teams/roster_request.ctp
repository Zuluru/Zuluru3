<?php
$this->Html->addCrumb(__('Teams'));
$this->Html->addCrumb($team->name);
$this->Html->addCrumb(__('Roster Request'));
$this->Html->addCrumb($person->full_name);
?>

<div class="people form">
	<h2><?= __('Roster Request') . ': ' . $team->name . ': ' . $person->full_name ?></h2>
<?php
echo $this->Html->para(null, __('You are requesting to join the team {0}.',
	$this->element('Teams/block', ['team' => $team, 'show_shirt' => false])) . ' ' .
	__('A coach or captain will have to approve your request before you are considered an active member of the team.'));

echo $this->Html->para(null, __('Possible roster roles are:'));
echo $this->Form->create($person, ['align' => 'horizontal']);
echo $this->Form->input('role', [
	'label' => false,
	'type' => 'radio',
	'options' => $roster_role_options,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
