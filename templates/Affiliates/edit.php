<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */

$this->Breadcrumbs->add(__('Affiliates'));
if ($affiliate->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($affiliate->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="affiliates form">
	<?= $this->Form->create($affiliate, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $affiliate->isNew() ? __('Create Affiliate') : __('Edit Affiliate') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'size' => 70,
]);
if (!$affiliate->isNew()) {
	echo $this->Form->control('active');
}
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Affiliates'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$affiliate->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['affiliate' => $affiliate->id]],
		['alt' => __('Delete'), 'title' => __('Delete Affiliate')],
		['confirm' => __('Are you sure you want to delete this affiliate?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Affiliate')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
