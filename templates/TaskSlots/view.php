<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

$this->Breadcrumbs->add(__('Task Slot'));
$this->Breadcrumbs->add(__('View'));
?>

<div class="task_slots view">
	<h2><?= __('Task Slot') ?></h2>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Task') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link($task_slot->task->name, ['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Task Date') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->date($task_slot->task_date) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Task Start') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->time($task_slot->task_start) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Task End') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->time($task_slot->task_end) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Assigned To') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('People/block', ['person' => $task_slot->person]) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Approved') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $task_slot->approved ? __('Yes') : __('No') ?></dd>
<?php
if ($task_slot->approved):
?>
		<dt class="col-sm-3 text-end"><?= __('Approved By') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('People/block', ['person' => $task_slot->approved_by]) ?></dd>
<?php
endif;
?>
	</dl>
</div>

<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['slot' => $task_slot->id]],
		['alt' => __('Edit'), 'title' => __('Edit Task Slot')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['slot' => $task_slot->id]],
		['alt' => __('Delete'), 'title' => __('Delete Task Slot')],
		['confirm' => __('Are you sure you want to delete this task slot?')]
	),
]);
?>
</div>
