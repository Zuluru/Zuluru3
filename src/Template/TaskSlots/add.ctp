<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

$this->Breadcrumbs->add($task->name);
$this->Breadcrumbs->add(__('Task Slots'));
$this->Breadcrumbs->add(__('Create'));
?>

<div class="task_slots form">
	<?= $this->Form->create($task_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Create Slots for the "{0}" Task', $task->name) ?></legend>
<?php
			echo $this->Form->control('task_date');
			echo $this->Form->control('task_start');
			echo $this->Form->control('task_end');
			echo $this->Form->control('number_of_slots', [
				'type' => 'number',
				'size' => 3,
				'default' => 1,
				'help' => __('The system will add this many slots at the specified time.'),
			]);
			echo $this->Form->control('days_to_repeat', [
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
