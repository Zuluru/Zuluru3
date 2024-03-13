<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

$this->Html->addCrumb($task->name);
$this->Html->addCrumb(__('Task Slots'));
$this->Html->addCrumb(__('Create'));
?>

<div class="task_slots form">
	<?= $this->Form->create($task_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Create Slots for the "{0}" Task', $task->name) ?></legend>
<?php
			echo $this->Form->input('task_date');
			echo $this->Form->input('task_start');
			echo $this->Form->input('task_end');
			echo $this->Form->input('number_of_slots', [
				'type' => 'number',
				'size' => 3,
				'default' => 1,
				'help' => __('The system will add this many slots at the specified time.'),
			]);
			echo $this->Form->input('days_to_repeat', [
				'type' => 'number',
				'size' => 3,
				'default' => 1,
				'help' => __('The system will add the specified number of slots at the specified time for this many consecutive days.'),
			]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
