<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Region $region
 * @var string[] $affiliates
 */

$this->Breadcrumbs->add(__('Regions'));
if ($region->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($region->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="regions form">
	<?= $this->Form->create($region, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $region->isNew() ? __('Create Region') : __('Edit Region') ?></legend>
<?php
echo $this->Form->i18nControls('name');
if ($region->isNew()) {
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
<?php
$links = [$this->Html->link(__('List Regions'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$region->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['region' => $region->id]],
		['alt' => __('Delete'), 'title' => __('Delete Region')],
		['confirm' => __('Are you sure you want to delete this region?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Region')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
