<?php
$this->Html->addCrumb (__('Registration'));
$this->Html->addCrumb (__('Add Payment Details from Email'));
?>

<div class="registrations form">
<h2><?php __('Handle Chase Error Email'); ?></h2>
<?php
if (!isset($fields)) {
	echo $this->Form->create(false, ['align' => 'horizontal']);
	echo $this->Form->input('email_text', [
		'cols' => 60,
		'rows' => 20,
	]);
} else {
	echo $this->Html->para(null, 'Looks like everything checks out. Click submit below to process the payment as it should have been. Registration will be marked as paid, teams created as required, etc.');
	echo $this->Form->create(false, ['url' => ['plugin' => 'Chase', 'controller' => 'Payment', 'action' => 'from_email_confirmation']]);
	echo $this->element('hidden', compact('fields'));
}

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
