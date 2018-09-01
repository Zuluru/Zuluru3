<fieldset>
	<legend><?= __('Paypal Options') ?></legend>
	<p><?= __('To find this information, log in to {0}, then go to Profile -> Profile and settings -> My selling tools -> Selling online -> API access -> Update, then Manage API Credentials or {1}.',
		$this->Html->link(__('PayPal'), 'https://paypal.com/'),
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
		$this->Html->link(__('PayPal Sandbox account'), 'https://developer.paypal.com/developer/accounts/')
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
?>
</fieldset>
