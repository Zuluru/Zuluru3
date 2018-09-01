<?php
if (!empty($tasks)):
?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Tasks') ?></th>
				<th><?= __('Time') ?></th>
				<th><?= __('Report To') ?></th>
				<th><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach ($tasks as $task):
?>
			<tr>
				<td class="splash_item"><?= $this->Html->link($task->name, ['controller' => 'Tasks', 'action' => 'view', 'task' => $task->id]) ?></td>
				<td class="splash_item"><?= $this->Time->day($task->task_slot->task_date) . ', ' .
					$this->Time->time($task->task_slot->task_start) . '-' .
					$this->Time->time($task->task_slot->task_end)
				?></td>
				<td class="splash_item"><?= $this->element('People/block', ['person' => $task->person]) ?></td>
				<td class="actions"><?php
					echo $this->Html->link(
						__('iCal'),
						['controller' => 'Task_slots', 'action' => 'ical', $task->task_slot->id, 'task.ics']);
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
