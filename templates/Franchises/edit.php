<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Franchise $franchise
 * @var string[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Franchises'));
if ($franchise->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($franchise->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="franchises form">
	<?= $this->Form->create($franchise, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $franchise->isNew() ? __('Create Franchise') : __('Edit Franchise') ?></legend>
<?php
echo $this->Form->control('name', [
	'help' => __('The full name of your franchise.'),
]);
if ($franchise->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
if (Configure::read('feature.urls')) {
	echo $this->Form->control('website', [
		'help' => __('Your franchise\'s website, if you have one.'),
	]);
}
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Franchises'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$franchise->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['franchise' => $franchise->id]],
		['alt' => __('Delete'), 'title' => __('Delete Franchise')],
		['confirm' => __('Are you sure you want to delete this franchise?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Franchise')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
