<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Event'));
$this->Html->addCrumb(h($event->name));
$this->Html->addCrumb(__('Connections'));
?>

<div class="events connections form">
	<?= $this->Form->create($event, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Event Connections') . ': ' . h($event->name) ?></legend>
		<fieldset>
<?php
echo __('These two lists connect this event to events that have gone before. They will typically be the same. For more details see the help for each field.');
echo $this->Form->input('predecessor._ids', [
	'label' => __('Events to consider as predecessors to this one:'),
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select all that apply'),
]);
echo $this->Form->input('successor_to._ids', [
	'label' => __('Events that this one is considered a successor to:'),
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select all that apply'),
]);
?>
		</fieldset>

		<fieldset>
<?php
echo __('These two lists connect this event to events that come later, and are generally not applicable when creating a new event. They will typically be the same. For more details see the help for each field.');
echo $this->Form->input('predecessor_to._ids', [
	'label' => __('Events that this one is considered a predecessor to:'),
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select all that apply'),
]);
echo $this->Form->input('successor._ids', [
	'label' => __('Events to consider as successors to this one:'),
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select all that apply'),
]);
?>
		</fieldset>

		<fieldset>
<?php
echo $this->Form->input('alternate._ids', [
	'label' => __('Events to consider as alternates to this one:'),
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select all that apply'),
]);
?>
		</fieldset>
	</fieldset>

	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>

</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
<?php
$this->Html->css(['jquery.asmselect.css'], ['block' => true]);
$this->Html->script(['jquery.asmselect.js'], ['block' => true]);
$this->Html->scriptBlock('jQuery("select[multiple]").asmSelect();', ['buffer' => true]);
