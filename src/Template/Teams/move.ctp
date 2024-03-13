<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Division[] $divisions
 */

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb($team->name);
$this->Html->addCrumb(__('Move'));
?>

<div class="teams move">
<h2><?= __('Move Team') . ': ' . $team->name ?></h2>

<?php
echo $this->Form->create($team, ['align' => 'horizontal']);
echo $this->Form->input('to', [
	'label' => __('Division to move this team to:'),
	'options' => collection($divisions)->combine('id', 'full_league_name')->toArray(),
]);

// TODO: Option for swapping this team with another, dynamically load team list into
// drop-down when "swap" checkbox is checked and a destination is selected

echo $this->Form->button(__('Move'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
