<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Franchise $franchise
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Franchises'));
if ($franchise->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($franchise->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="franchises form">
	<?= $this->Form->create($franchise, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $franchise->isNew() ? __('Create Franchise') : __('Edit Franchise') ?></legend>
<?php
echo $this->Form->input('name', [
	'help' => __('The full name of your franchise.'),
]);
if ($franchise->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
if (Configure::read('feature.urls')) {
	echo $this->Form->input('website', [
		'help' => __('Your franchise\'s website, if you have one.'),
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
echo $this->Html->tag('li', $this->Html->link(__('List Franchises'), ['action' => 'index']));
if (!$franchise->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'franchise' => $franchise->id],
		['alt' => __('Delete'), 'title' => __('Delete Franchise')],
		['confirm' => __('Are you sure you want to delete this franchise?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Franchise')]));
}
?>
	</ul>
</div>
