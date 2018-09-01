<?php
$this->Html->addCrumb(__('Franchise'));
$this->Html->addCrumb($franchise->name);
$this->Html->addCrumb(__('Add Team to Franchise'));
?>

<div class="franchises add_team">
<h2><?= __('Add Team') . ': ' . $franchise->name ?></h2>

<?php
echo $this->Html->para(null, __('Select a team from your history below to add to this franchise.'));
echo $this->Html->para('highlight-message', __('Note that you can only add teams that you are a captain, assistant captain or coach of. This may necessitate temporarily transferring this franchise to someone else.'));
$options = [];
foreach ($teams as $team) {
	if (!empty($team->division_id)) {
		$options[$team->id] = "{$team->name} ({$team->division->full_league_name})";
	} else {
		$options[$team->id] = "{$team->name} (" . __('unassigned') . ')';
	}
}
echo $this->Form->create($franchise, ['align' => 'horizontal']);
echo $this->Form->input('team_id', [
		'label' => false,
		'options' => $options,
		'empty' => '-- select from list --',
]);
echo $this->Form->button(__('Add team'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
