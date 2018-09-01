<?php
$this->Html->addCrumb(__('Upload Type'));
if ($upload_type->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($upload_type->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="upload_types form">
	<?= $this->Form->create($upload_type, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $upload_type->isNew() ? __('Create Upload Type') : __('Edit Upload Type') ?></legend>
<?php
echo $this->Form->input('name');
if ($upload_type->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
// TODO: Add an optional way to manage a blank document for download
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Upload Types'), ['action' => 'index']));
if (!$upload_type->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'type' => $upload_type->id],
		['alt' => __('Delete'), 'title' => __('Delete Upload Type')],
		['confirm' => __('Are you sure you want to delete this uploadType?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Upload Type')]));
}
?>
	</ul>
</div>
