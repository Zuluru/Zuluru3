<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

$this->Breadcrumbs->add(__('Task Slots'));
$this->Breadcrumbs->add(__('Edit'));
?>

<div class="task_slots form">
	<?= $this->Form->create($task_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Edit Task Slot') ?></legend>
<?php
echo $this->Form->control('task_date');
echo $this->Form->control('task_start');
echo $this->Form->control('task_end');
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['slot' => $task_slot->id]],
		['alt' => __('Delete'), 'title' => __('Delete Task Slot')],
		['confirm' => __('Are you sure you want to delete this task slot?')]
	),
]);
?>
</div>
