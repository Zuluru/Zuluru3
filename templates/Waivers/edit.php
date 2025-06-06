<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Waiver $waiver
 * @var string[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Waivers'));
if ($waiver->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($waiver->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="waivers form">
	<?= $this->Form->create($waiver, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $waiver->isNew() ? __('Create Waiver') : __('Edit Waiver') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'size' => 60,
	'help' => __('Full name of this waiver.'),
]);
if ($waiver->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
echo $this->Form->i18nControls('description', [
	'size' => 60,
	'help' => __('An extended description, shown solely to administrators, for example to differentiate between various "Membership" waivers.'),
]);
if (!isset($can_edit_text) || $can_edit_text) {
	echo $this->Form->i18nControls('text', [
		'cols' => 60,
		'rows' => 30,
		'help' => __('Complete waiver text, HTML is allowed.'),
		'class' => 'wysiwyg_advanced',
	]);
} else {
	echo $this->Html->para('highlight-message', __('This waiver has already been signed, so for legal reasons the text cannot be edited.'));
}
echo $this->Form->control('active');

$selectors = Configure::read('options.waivers.expiry_type');
foreach (array_keys($selectors) as $key) {
	$selectors[$key] = "#{$key}_options";
}
echo $this->Jquery->toggleInput('expiry_type', [
	'empty' => '---',
], [
	'values' => $selectors,
]);
?>

		<fieldset id="start_and_end_options">
			<legend><?= __('Expiry Options') ?></legend>
			<div id="fixed_dates_options">
<?php
$months = [];
for ($month = 1; $month <= 12; ++ $month) {
	$months[$month] = \Cake\I18n\FrozenDate::create(2000, $month, 1)->format('F');
}
echo $this->Form->control('start_month', [
	'label' => __('From month'),
	'options' => $months,
]);
echo $this->Form->control('start_day', [
	'type' => 'day',
	'label' => __('From day'),
]);
echo $this->Form->control('end_month', [
	'label' => __('Through month'),
	'options' => $months,
]);
echo $this->Form->control('end_day', [
	'type' => 'day',
	'label' => __('Through day'),
]);
?>
			</div>
			<div id="elapsed_time_options">
<?php
echo $this->Form->control('duration', [
	'size' => 5,
	'help' => ' ' . __('days'),
]);
?>
			</div>
			<div id="event_options">
<?php
echo $this->Html->para(null, __('Event waivers have no expiry options; they always expire after the event is done.'));
?>
			</div>
			<div id="never_options">
<?php
echo $this->Html->para(null, __('Waivers that never expire have no expiry options.'));
?>
			</div>
		</fieldset>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Waivers'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$waiver->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['waiver' => $waiver->id]],
		['alt' => __('Delete'), 'title' => __('Delete Waiver')],
		['confirm' => __('Are you sure you want to delete this waiver?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Waiver')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
