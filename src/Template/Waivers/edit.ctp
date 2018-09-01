<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Waivers'));
if ($waiver->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($waiver->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="waivers form">
	<?= $this->Form->create($waiver, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $waiver->isNew() ? __('Create Waiver') : __('Edit Waiver') ?></legend>
<?php
echo $this->Form->input('name', [
	'size' => 60,
	'help' => __('Full name of this waiver.'),
]);
if ($waiver->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
echo $this->Form->input('description', [
	'size' => 60,
	'help' => __('An extended description, shown solely to administrators, for example to differentiate between various "Membership" waivers.'),
]);
if (!isset($can_edit_text) || $can_edit_text) {
	echo $this->Form->input('text', [
		'cols' => 60,
		'rows' => 30,
		'help' => __('Complete waiver text, HTML is allowed.'),
		'class' => 'wysiwyg_advanced',
	]);
} else {
	echo $this->Html->para('highlight-message', __('This waiver has already been signed, so for legal reasons the text cannot be edited.'));
}
echo $this->Form->input('active');

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
echo $this->Form->input('start_month', [
	'type' => 'month',
	'label' => __('From month'),
]);
echo $this->Form->input('start_day', [
	'type' => 'day',
	'label' => __('From day'),
]);
echo $this->Form->input('end_month', [
	'type' => 'month',
	'label' => __('Through month'),
]);
echo $this->Form->input('end_day', [
	'type' => 'day',
	'label' => __('Through day'),
]);
?>
			</div>
			<div id="elapsed_time_options">
<?php
echo $this->Form->input('duration', [
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
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Waivers'), ['action' => 'index']));
if (!$waiver->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'waiver' => $waiver->id],
		['alt' => __('Delete'), 'title' => __('Delete Waiver')],
		['confirm' => __('Are you sure you want to delete this waiver?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Waiver')]));
}
?>
	</ul>
</div>
