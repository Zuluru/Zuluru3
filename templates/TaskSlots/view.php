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
	<dl class="dl-horizontal">
		<dt><?= __('Task') ?></dt>
		<dd><?= $this->Html->link($task_slot->task->name, ['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]) ?></dd>
		<dt><?= __('Task Date') ?></dt>
		<dd><?= $this->Time->date($task_slot->task_date) ?></dd>
		<dt><?= __('Task Start') ?></dt>
		<dd><?= $this->Time->time($task_slot->task_start) ?></dd>
		<dt><?= __('Task End') ?></dt>
		<dd><?= $this->Time->time($task_slot->task_end) ?></dd>
		<dt><?= __('Assigned To') ?></dt>
		<dd><?= $this->element('People/block', ['person' => $task_slot->person]) ?></dd>
		<dt><?= __('Approved') ?></dt>
		<dd><?= $task_slot->approved ? __('Yes') : __('No') ?></dd>
<?php
if ($task_slot->approved):
?>
		<dt><?= __('Approved By') ?></dt>
		<dd><?= $this->element('People/block', ['person' => $task_slot->approved_by]) ?></dd>
<?php
endif;
?>
	</dl>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', '?' => ['slot' => $task_slot->id]],
	['alt' => __('Edit'), 'title' => __('Edit Task Slot')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', '?' => ['slot' => $task_slot->id]],
	['alt' => __('Delete'), 'title' => __('Delete Task Slot')],
	['confirm' => __('Are you sure you want to delete this task slot?')]));
?>
	</ul>
</div>
