<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Badges'));
if ($badge->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($badge->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="badges form">
	<?= $this->Form->create($badge, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $badge->isNew() ? __('Create Badge') : __('Edit Badge') ?></legend>
<?php
echo $this->Form->input('name', [
	'size' => 70,
	'help' => __('The full name of the badge, to be used as title text on the icon.'),
]);
if ($badge->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
echo $this->Form->input('description', [
	'size' => 70,
	'help' => __('A detailed description of this badge, which should explain how to earn it, what it denotes, and/or what the benefits are.'),
]);
echo $this->Form->input('category', [
	'options' => Configure::read('options.category'),
	'hide_single' => true,
	'empty' => '---',
	'help' => __('The category determines the timing for when the badge may be awarded. Don\'t change this unless you know what you are doing.'),
]);
echo $this->Form->input('handler', [
	'size' => 70,
	'help' => __('The handler sets which algorithm is used to determine whether a badge should be awarded. Don\'t change this unless you REALLY know what you are doing.'),
]);
// TODOBOOTSTRAP: Template change to put the checkbox label on the left side
if ($badge->isNew()) {
	echo $this->Form->hidden('active', ['value' => true]);
} else {
	echo $this->Form->input('active');
}
echo $this->Form->input('visibility', [
	'options' => Configure::read('options.visibility'),
	'hide_single' => true,
	'empty' => '---',
	'help' => __('Select where this badge will be visible.'),
]);
// TODO: Icon upload option?
echo $this->Form->input('icon', [
	'help' => __('Include only the base name of the file; _32.png, _48.png and _64.png will be appended as required.'),
]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) // TODOBOOTSTRAP: CSS to move the Submit button to line up with input fields ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Badges'), ['action' => 'index']));
if (!$badge->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'badge' => $badge->id],
		['alt' => __('Delete'), 'title' => __('Delete Badge')],
		['confirm' => __('Are you sure you want to delete this badge?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Badge')]));
}
?>
	</ul>
</div>
