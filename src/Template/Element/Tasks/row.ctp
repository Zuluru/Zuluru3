<?php
use Cake\Core\Configure;

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
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
		echo $this->Jquery->ajaxInput("{$task_slot->id}.person_id", [
			'selector' => 'tr',
			'url' => ['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $task_slot->id],
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
				'url' => ['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $task_slot->id, 'person' => Configure::read('Perm.my_id')],
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
	} else if ((Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) && $task_slot->person_id) {
		echo $this->Html->tag('span',
			$this->Jquery->ajaxLink(__('Approve'), [
				'url' => ['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $task_slot->id],
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
		['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $task_slot->id],
		['alt' => __('View'), 'title' => __('View')]);
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
		echo $this->Html->iconLink('edit_24.png',
			['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $task_slot->id],
			['alt' => __('Edit'), 'title' => __('Edit')]);
		echo $this->Form->iconPostLink('delete_24.png',
			['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $task_slot->id],
			['alt' => __('Delete'), 'title' => __('Delete')],
			['confirm' => __('Are you sure you want to delete this taskSlot?')]);
	}
	?></td>
</tr>
