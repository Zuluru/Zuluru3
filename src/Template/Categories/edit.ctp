<?php
$this->Html->addCrumb(__('Categories'));
if ($category->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($category->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="categories form">
	<?= $this->Form->create($category, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $category->isNew() ? __('Create Category') : __('Edit Category') ?></legend>
<?php
echo $this->Form->input('name', [
		'size' => 100,
]);
if ($category->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Categories'), ['action' => 'index']));
if (!$category->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'category' => $category->id],
		['alt' => __('Delete'), 'title' => __('Delete Category')],
		['confirm' => __('Are you sure you want to delete this category?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Category')]));
}
?>
	</ul>
</div>
