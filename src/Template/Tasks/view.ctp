<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Tasks'));
$this->Breadcrumbs->add(h($task->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="tasks view">
	<h2><?= h($task->name) ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('Category') ?></dt>
		<dd><?= $this->Html->link($task->category->name, ['controller' => 'Categories', 'action' => 'view', 'category' => $task->category->id]) ?></dd>
		<dt><?= __('Description') ?></dt>
		<dd><?= $task->description ?></dd>
<?php
if ($this->Authorize->can('edit', $task)):
?>
		<dt><?= __('Notes') ?></dt>
		<dd><?= $task->notes ?></dd>
		<dt><?= __('Auto-Approve') ?></dt>
		<dd><?= $task->auto_approve ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Allow Signup') ?></dt>
		<dd><?= $task->allow_signup ? __('Yes') : __('No') ?></dd>
<?php
endif;
?>
		<dt><?= __('Reporting To') ?></dt>
		<dd><?= $this->element('People/block', ['person' => $task->person]) ?></dd>
	</dl>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Tasks')]));
if ($this->Authorize->can('edit', $task)) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', 'task' => $task->id],
		['alt' => __('Edit'), 'title' => __('Edit Task')]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'task' => $task->id],
		['alt' => __('Delete'), 'title' => __('Delete Task')],
		['confirm' => __('Are you sure you want to delete this task?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('schedule_add_32.png',
		['controller' => 'Task_slots', 'action' => 'add', 'task' => $task->id],
		['alt' => __('Add Slots'), 'title' => __('Add Slots')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add Task'), 'title' => __('Add Task')]));
}
?>
	</ul>
</div>
<?php
if (!empty($task->task_slots)):
?>

<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Related Task Slots') ?></h4>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Task Date') ?></th>
						<th><?= __('Task Start') ?></th>
						<th><?= __('Task End') ?></th>
						<th><?= __('Assigned To') ?></th>
						<th><?= __('Approved By') ?></th>
						<th class="actions"><?= __('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($task->task_slots as $task_slot) {
		echo $this->element('Tasks/row', compact('task', 'task_slot'));
	}
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
endif;
