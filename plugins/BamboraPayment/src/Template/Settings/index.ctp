<?php

use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb('Bambora');
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);
?>
<fieldset>
	<legend><?= __('{0} Settings', 'Bambora') ?></legend>
	<p><?= __('To configure {0} to work with {1}, log into their {2}, go to {3} -> {4} -> {5}, then:',
		'Bambora', ZULURU,
			$this->Html->link(__('Member Area'), 'https://web.na.bambora.com/'),
			'Administration', 'Account Settings', 'Order Settings')
	?></p>
	<ol>
		<li><?= __('Set both "{0}" and "{1}" to {2}',
			'Approval Redirect',
				'Decline Redirect',
			// TODO: Add links here to copy values to the clipboard
			Router::url(['plugin' => 'BamboraPayment', 'controller' => 'Payment', 'action' => 'index'], true)
		) ?></li>
		<li><?= __('Add {0} to the list of "{1}"',
			$_SERVER['HTTP_HOST'],
			'Allowed domain names'
		) ?></li>
		<li><?= __('Ensure that "{0}" is selected, and "{1}" is selected as the "{2}". Enter the "{3}" below.',
			'Require hash validation on all Payment Gateway transaction requests',
			'SHA-1',
			'Hash algorithm',
			'Hash key'
		) ?></li>
		<li><?= __('Ensure that "{0}" is checked',
			'Include hash validation in Transaction Response Page redirection and Payment Gateway Response Notification'
		) ?></li>
		<li><?= __('We also strongly recommend checking "{0}" on the {1} -> {2} page. This should prevent duplicate payments from being taken.',
			'Require unique order numbers',
			'Configuration',
			'Payment Profile Configuration'
		) ?></li>
		<li><?= __('Additional form configuration details can be found in the {0}.',
			$this->Html->link(__('{0} documentation', 'Bambora'), 'https://dev.na.bambora.com/docs/guides/checkout/form/', ['target' => '_new'])
		) ?></li>
	</ol>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'bambora_live_merchant_id',
	'options' => [
		'label' => __('Merchant ID'),
		'help' => __('This will be a 9 digit number, displayed in the upper-right corner of the Bambora dashboard.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'bambora_live_hash_key',
	'options' => [
		'label' => __('Hash key'),
		'help' => __('This will be a 32 character alphanumeric string.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'bambora_test_merchant_id',
	'options' => [
		'label' => __('Test merchant ID'),
		'help' => __('Required only if you are testing payments. You will need to create a {0} with Bambora to do this.',
			$this->Html->link(__('test account'), 'https://dev.na.bambora.com/docs/forms/create_test_merchant_account')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'bambora_test_hash_key',
	'options' => [
		'label' => __('Test hash key'),
		'help' => __('Required only if you are testing payments. You will need to create a {0} with Bambora to do this.',
			$this->Html->link(__('test account'), 'https://dev.na.bambora.com/docs/forms/create_test_merchant_account')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'bambora_refunds',
	'options' => [
		'label' => __('Issue refunds online'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, refunds for payments received through {0} can be issued though {0}.', 'Bambora'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
