<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 */

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Roster Request'));
$this->Breadcrumbs->add($person->full_name);
?>

<div class="people form">
	<h2><?= __('Roster Request') . ': ' . $team->name . ': ' . $person->full_name ?></h2>
<?php
echo $this->Html->para(null, __('You are requesting to join the team {0}.',
	$this->element('Teams/block', ['team' => $team, 'show_shirt' => false])) . ' ' .
	__('A coach or captain will have to approve your request before you are considered an active member of the team.'));

echo $this->Html->para(null, __('Possible roster roles are:'));
echo $this->Form->create($person, ['align' => 'horizontal']);
echo $this->Form->control('role', [
	'label' => false,
	'type' => 'radio',
	'options' => $roster_role_options,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
