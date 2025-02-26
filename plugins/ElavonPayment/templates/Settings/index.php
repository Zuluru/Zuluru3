<?php

use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add('Elavon');
?>

<div class="settings form">
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
?>
<fieldset>
	<legend class="border-bottom"><?= __('{0} Settings', 'Elavon') ?></legend>
	<p><?= __('To configure {0} to work with {1}, log into their {2}, go to {3} -> {4} -> {5}, then:',
		'Elavon', ZULURU,
			$this->Html->link(__('Payment Portal'), 'https://www.convergepay.com/converge-webapp/#!/login'),
			'Settings', 'Hosted Payment', 'Payment Page Setup')
	?></p>
	<ol>
		<li><?= __('For "{0}", set "{1}" to {2}',
			'Payment Page',
			'Cancel Link',
			// TODO: Add links here to copy values to the clipboard
			Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'checkout'], true)
		) ?></li>
		<li><?= __('For "{0}" -> "{1}", set "{2}" to {3}',
			'Response Page',
			'Decline Page',
			'Button Link',
			Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'checkout'], true)
		) ?></li>
		<li><?= __('For "{0}" -> "{1}", set "{2}" to {3}',
			'Response Page',
			'Redirect URL',
			'Redirect URL',
			Router::url(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index'], true)
		) ?></li>
		<li><?= __('Additional form configuration details can be found in the {0}.',
			$this->Html->link(__('{0} documentation', 'Elavon'), 'https://support.convergepay.com/s/article/Customize-the-Hosted-Payments-Page', ['target' => '_new'])
		) ?></li>
	</ol>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_live_merchant_id',
	'options' => [
		'label' => __('Merchant ID'),
		'help' => __('This will be a 7 digit number, displayed in the upper-right corner of the Elavon dashboard.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_live_merchant_user_id',
	'options' => [
		'label' => __('Merchant User ID'),
		'help' => __('This can be found in {0} -> {1} -> {2}.',
			'Settings',
			'Online API Security Allowed List Manager',
			'Registered API Users'
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_live_pin',
	'options' => [
		'label' => __('PIN'),
		'help' => __('This will be a 64 character alphanumeric string, found with the "{0}" link in the lower-right corner of the employee details page.',
			'Show PIN'
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_test_merchant_id',
	'options' => [
		'label' => __('Test merchant ID'),
		'help' => __('Required only if you are testing payments. You will need to request a {0} with Elavon to do this.',
			$this->Html->link(__('demo account'), 'https://developer.elavon.com/products/converge/v1/getting-started#contact-elavon-support')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_test_merchant_user_id',
	'options' => [
		'label' => __('Test merchant user ID'),
		'help' => __('Required only if you are testing payments. You will need to request a {0} with Elavon to do this.',
			$this->Html->link(__('demo account'), 'https://developer.elavon.com/products/converge/v1/getting-started#contact-elavon-support')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_test_pin',
	'options' => [
		'label' => __('Test PIN'),
		'help' => __('Required only if you are testing payments. You will need to request a {0} with Elavon to do this.',
			$this->Html->link(__('demo account'), 'https://developer.elavon.com/products/converge/v1/getting-started#contact-elavon-support')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'elavon_refunds',
	'options' => [
		'label' => __('Issue refunds online'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, refunds for payments received through {0} can be issued though {0}.', 'Elavon'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
