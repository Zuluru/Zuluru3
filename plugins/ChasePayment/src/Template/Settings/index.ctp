<?php

use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb('Chase Paymentech');
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);
?>
<fieldset>
	<legend><?= __('{0} Settings', 'Chase Paymentech') ?></legend>
	<fieldset>
		<legend><?= __('Live Payments') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_store',
	'options' => [
		'label' => __('Payment page ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_password',
	'options' => [
		'label' => __('Transaction key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_response',
	'options' => [
		'label' => __('Response key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_gateway_id',
	'options' => [
		'label' => __('Gateway ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_gateway_password',
	'options' => [
		'label' => __('Gateway password'),
	],
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Test Payments') ?></legend>
		<p><strong><?= __('These test settings are only required if you are doing test payments through {0}', $this->Html->link('rpm.demo.e-xact.com', 'https://rpm.demo.e-xact.com/')) ?></strong></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_store',
	'options' => [
		'label' => __('Payment page ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_password',
	'options' => [
		'label' => __('Transaction key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_response',
	'options' => [
		'label' => __('Response key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_gateway_id',
	'options' => [
		'label' => __('Gateway ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_gateway_password',
	'options' => [
		'label' => __('Gateway password'),
	],
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Other Settings') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_refunds',
	'options' => [
		'label' => __('Issue refunds online'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, refunds for payments received through {0} can be issued though {0}.', 'Chase'),
	],
]);
?>
	</fieldset>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
