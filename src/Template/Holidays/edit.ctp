<?php
$this->Html->addCrumb(__('Holiday'));
if ($holiday->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($holiday->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="holidays form">
	<?= $this->Form->create($holiday, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $holiday->isNew() ? __('Create Holiday') : __('Edit Holiday') ?></legend>
<?php
echo $this->Form->input('name');
if ($holiday->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
echo $this->Form->input('date');
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Holidays'), ['action' => 'index']));
if (!$holiday->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'holiday' => $holiday->id],
		['alt' => __('Delete'), 'title' => __('Delete Holiday')],
		['confirm' => __('Are you sure you want to delete this holiday?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Holiday')]));
}
?>
	</ul>
</div>
