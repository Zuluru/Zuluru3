<?php
$this->Html->addCrumb(__('Team Events'));
$this->Html->addCrumb(__('Create'));
$this->Html->addCrumb(__('Dates'));
?>

<div class="team_events form">
	<?= $this->Form->create($team_event, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Team Event Dates') ?></legend>
<?php
for ($i = 0; $i < $this->request->data['repeat_count']; ++ $i) {
	echo $this->Form->input("dates.$i.date", ['type' => 'date']);
}
unset($this->request->data['dates']);
echo $this->element('hidden', ['fields' => $this->request->data]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
