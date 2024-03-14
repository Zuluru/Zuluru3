<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Roster Role'));
$this->Breadcrumbs->add($person->full_name);
?>

<div class="people form">
	<h2><?= __('Roster Role') . ': ' . $team->name . ': ' . $person->full_name ?></h2>
<?php
$roster_descriptions = Configure::read('options.roster_role');
echo $this->Html->para(null, __('You are attempting to change the role for {0} on the team {1}.',
	$this->element('People/block', compact('person')),
	$this->element('Teams/block', ['team' => $team, 'show_shirt' => false])));
echo $this->Html->para(null, __('Current role:') . ' ' .
	$this->Html->tag('strong', __($roster_descriptions[$role])));

echo $this->Html->para(null, __('Possible roster roles are:'));
echo $this->Form->create($person, ['align' => 'horizontal']);
echo $this->Form->control('role', [
	'label' => false,
	'type' => 'radio',
	'options' => $roster_role_options,
	'default' => $role,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
