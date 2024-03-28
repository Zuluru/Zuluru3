<?php

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add('PayPal');
?>

<div class="settings form">
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
?>
<fieldset>
	<legend><?= __('{0} Settings', 'PayPal') ?></legend>
	<p><?= __('To find this information, log in to {0}, then go to Profile -> Profile and settings -> My selling tools -> Selling online -> API access -> Update, then Manage API Credentials or {1}.',
		$this->Html->link('PayPal', 'https://paypal.com/'),
		$this->Html->link(__('View API signature'), 'https://www.paypal.com/ca/cgi-bin/webscr?cmd=_profile-api-signature')
	) ?></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_live_user',
	'options' => [
		'label' => __('Live API username'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_live_password',
	'options' => [
		'label' => __('Live API password'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_live_signature',
	'options' => [
		'label' => __('Live signature'),
	],
]);
?>
	<p><?= __('To do any testing of your registration system, you need a {0}, then click the facilitator address -> Profile -> API Credentials.',
		$this->Html->link(__('{0} Sandbox account', 'PayPal'), 'https://developer.paypal.com/developer/accounts/')
	) ?></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_test_user',
	'options' => [
		'label' => __('Sandbox API username'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_test_password',
	'options' => [
		'label' => __('Sandbox API password'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_test_signature',
	'options' => [
		'label' => __('Sandbox signature'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'paypal_refunds',
	'options' => [
		'label' => __('Issue refunds online'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, refunds for payments received through {0} can be issued though {0}.', 'PayPal'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
