<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Tasks'));
$this->Html->addCrumb(__('List'));
?>

<div class="tasks index">
	<h2><?= __('Tasks') ?></h2>
<?php
if (empty($tasks)):
	echo $this->Html->para(null, __('No tasks available.'));
else:
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Task') ?></th>
					<th><?= __('Category') ?></th>
					<th><?= __('Reporting To') ?></th>
<?php
	if ($this->Authorize->can('edit', current($tasks))):
?>
					<th><?= __('Auto-Approve') ?></th>
					<th><?= __('Allow Signup') ?></th>
<?php
	endif;
?>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($tasks as $task):
?>
				<tr>
					<td><?= $task->name ?></td>
					<td><?= $this->Html->link($task->category->name, ['controller' => 'Categories', 'action' => 'view', 'category' => $task->category->id]) ?></td>
					<td><?= $this->element('People/block', ['person' => $task->person]) ?></td>
<?php
		if ($this->Authorize->can('edit', $task)):
?>
					<td><?= $task->auto_approve ? __('Yes') : __('No') ?></td>
					<td><?= $task->allow_signup ? __('Yes') : __('No') ?></td>
<?php
		endif;
?>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'task' => $task->id],
							['alt' => __('View'), 'title' => __('View')]);
						if ($this->Authorize->can('edit', $task)) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'edit', 'task' => $task->id],
								['alt' => __('Edit'), 'title' => __('Edit')]);
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete', 'task' => $task->id],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this task?')]);
							echo $this->Html->iconLink('schedule_add_24.png',
								['controller' => 'Task_slots', 'action' => 'add', 'task' => $task->id],
								['alt' => __('Add Slots'), 'title' => __('Add Slots')]);
						}
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>
</div>
<?php
if ($this->Authorize->can('add', \App\Controller\TasksController::class)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Task')]));
?>
	</ul>
</div>
<?php
endif;
