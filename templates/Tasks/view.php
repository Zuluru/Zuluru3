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
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Category') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link($task->category->name, ['controller' => 'Categories', 'action' => 'view', '?' => ['category' => $task->category->id]]) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Description') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $task->description ?></dd>
<?php
if ($this->Authorize->can('edit', $task)):
?>
		<dt class="col-sm-3 text-end"><?= __('Notes') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $task->notes ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Auto-Approve') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $task->auto_approve ? __('Yes') : __('No') ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Allow Signup') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $task->allow_signup ? __('Yes') : __('No') ?></dd>
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Reporting To') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('People/block', ['person' => $task->person]) ?></dd>
	</dl>
</div>

<div class="actions columns">
<?php
$links = [
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Tasks')]
	)
];
if ($this->Authorize->can('edit', $task)) {
	$links[] = $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['task' => $task->id]],
		['alt' => __('Edit'), 'title' => __('Edit Task')]
	);
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['task' => $task->id]],
		['alt' => __('Delete'), 'title' => __('Delete Task')],
		['confirm' => __('Are you sure you want to delete this task?')]
	);
	$links[] = $this->Html->iconLink('schedule_add_32.png',
		['controller' => 'Task_slots', 'action' => 'add', '?' => ['task' => $task->id]],
		['alt' => __('Add Slots'), 'title' => __('Add Slots')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add Task'), 'title' => __('Add Task')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
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
