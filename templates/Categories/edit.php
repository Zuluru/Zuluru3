<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category $category
 * @var \App\Model\Entity\Affiliate[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Categories'));
if ($category->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($category->name));
	$this->Breadcrumbs->add(__('Edit'));
}

$types = Configure::read('options.category_types');
$multiple_types = (count($types) > 1);
?>

<div class="categories form">
	<?= $this->Form->create($category, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $category->isNew() ? __('Create Category') : __('Edit Category') ?></legend>
<?php
if ($multiple_types) {
	echo $this->Form->control('type', [
		'options' => $types,
	]);
} else {
	echo $this->Form->hidden('type', [
		'value' => array_key_first($types),
	]);
}
echo $this->Form->control('name', [
	'size' => 100,
]);
echo $this->Form->control('slug', [
	'size' => 100,
	'help' => __('Unique identifier to be used in URLs for this category. Should be entirely lower case, numbers, hyphens or underscores.'),
]);
echo $this->Form->control('image_url', [
	'label' => __('Image URL'),
	'size' => 255,
	'help' => __('URL of the image to use for this category.'),
]);
echo $this->Form->control('description', [
	'cols' => 70,
	'rows' => 5,
	'class' => 'wysiwyg_advanced',
]);
if ($category->isNew()) {
	echo $this->Form->control('affiliate_id', [
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
		['action' => 'delete', '?' => ['category' => $category->id]],
		['alt' => __('Delete'), 'title' => __('Delete Category')],
		['confirm' => __('Are you sure you want to delete this category?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Category')]));
}
?>
	</ul>
</div>
