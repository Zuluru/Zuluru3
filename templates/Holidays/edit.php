<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Holiday $holiday
 * @var string[] $affiliates
 */

$this->Breadcrumbs->add(__('Holiday'));
if ($holiday->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($holiday->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="holidays form">
	<?= $this->Form->create($holiday, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $holiday->isNew() ? __('Create Holiday') : __('Edit Holiday') ?></legend>
<?php
echo $this->Form->i18nControls('name');
if ($holiday->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
echo $this->Form->control('date');
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Holidays'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$holiday->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['holiday' => $holiday->id]],
		['alt' => __('Delete'), 'title' => __('Delete Holiday')],
		['confirm' => __('Are you sure you want to delete this holiday?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Holiday')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
