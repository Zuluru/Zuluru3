<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskSlot $task_slot
 */
?>
<?= $this->element('Tasks/row', ['task_slot' => $task_slot, 'task' => $task_slot->task]);
