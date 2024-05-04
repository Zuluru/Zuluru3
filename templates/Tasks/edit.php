<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 */

$this->Breadcrumbs->add(__('Tasks'));
if ($task->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($task->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="tasks form">
	<?= $this->Form->create($task, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $task->isNew() ? __('Create Task') : __('Edit Task') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'size' => 100,
]);
echo $this->Form->control('category_id');
echo $this->Form->i18nControls('description', [
	'help' => __('This description will be visible to people assigned to the task.'),
]);
echo $this->Form->i18nControls('notes', [
	'help' => __('Notes will only be visible administrators.'),
]);
echo $this->Form->control('auto_approve', [
	'help' => __('If checked, assignments will not require separate admin approval.'),
]);
echo $this->Form->control('allow_signup', [
	'help' => __('If checked, volunteers will be able to sign themselves up; if not, an admin will have to assign people.'),
]);
echo $this->Form->control('person_id', [
	'label' => __('Reporting To'),
	'empty' => '---',
]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Tasks'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$task->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['task' => $task->id]],
		['alt' => __('Delete'), 'title' => __('Delete Task')],
		['confirm' => __('Are you sure you want to delete this task?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Task')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
