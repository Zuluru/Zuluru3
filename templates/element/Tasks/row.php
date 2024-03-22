<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskSlot $task_slot
 * @var string[] $people
 */


use App\Authorization\ContextResource;

$class = null;
if (empty($task_slot->person_id)) {
	$class = ' class="unpublished"';
}
?>
<tr <?= $class ?>>
	<td><?= $this->Time->date($task_slot->task_date) ?></td>
	<td><?= $this->Time->time($task_slot->task_start) ?></td>
	<td><?= $this->Time->time($task_slot->task_end) ?></td>
	<td><?php
	if ($this->Authorize->can('assign', new ContextResource($task_slot, ['task' => $task]))) {
		echo $this->Jquery->ajaxInput("{$task_slot->id}.person_id", [
			'selector' => 'tr',
			'url' => ['controller' => 'TaskSlots', 'action' => 'assign', '?' => ['slot' => $task_slot->id]],
			'disposition' => 'replace_closest',
			'param-name' => 'person',
		], [
			'label' => false,
			'empty' => '---',
			'options' => $people,
			'default' => $task_slot->person_id,
		]);
	} else if (!empty($task_slot->person_id)) {
		echo $this->element('People/block', ['person' => $task_slot->person]);
	} else {
		echo $this->Html->tag('span',
			$this->Jquery->ajaxLink(__('Sign up'), [
				'url' => ['controller' => 'TaskSlots', 'action' => 'assign', '?' => ['slot' => $task_slot->id, 'person' => $this->Identity->getId()]],
				// Need to replace the whole row
				'disposition' => 'replace_closest',
				'selector' => 'tr',
			]),
			['class' => 'actions']
		);
	}
	?></td>
	<td><?php
	if (!empty($task_slot->approved_by_id)) {
		echo $this->element('People/block', ['person' => $task_slot->approved_by]);
	} else if ($this->Authorize->can('approve', $task_slot)) {
		echo $this->Html->tag('span',
			$this->Jquery->ajaxLink(__('Approve'), [
				'url' => ['controller' => 'TaskSlots', 'action' => 'approve', '?' => ['slot' => $task_slot->id]],
				// Need to replace the .actions span
				'disposition' => 'replace_closest',
				'selector' => 'span',
			]),
			['class' => 'actions']
		);
	} else {
		echo __('No');
	}
	?></td>
	<td class="actions"><?php
	echo $this->Html->iconLink('view_24.png',
		['controller' => 'TaskSlots', 'action' => 'view', '?' => ['slot' => $task_slot->id]],
		['alt' => __('View'), 'title' => __('View')]);
	if ($this->Authorize->can('edit', $task_slot)) {
		echo $this->Html->iconLink('edit_24.png',
			['controller' => 'TaskSlots', 'action' => 'edit', '?' => ['slot' => $task_slot->id]],
			['alt' => __('Edit'), 'title' => __('Edit')]);
		echo $this->Form->iconPostLink('delete_24.png',
			['controller' => 'TaskSlots', 'action' => 'delete', '?' => ['slot' => $task_slot->id]],
			['alt' => __('Delete'), 'title' => __('Delete')],
			['confirm' => __('Are you sure you want to delete this task slot?')]);
	}
	?></td>
</tr>
