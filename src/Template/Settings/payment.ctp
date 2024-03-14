<?php
/**
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add(__('Online Payments'));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$empty = __('Use default');
} else {
	$empty = false;
}
echo $this->form->create(null, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Common Options') ?></legend>
		<p class="warning-message"><?= __('Note that selection and configuration of your payment provider has moved to the Plugins section of the Configuration menu') ?></p>
<?php
	if (!$affiliate) {
		echo $this->element('Settings/input', [
			'category' => 'payment',
			'name' => 'options',
			'options' => [
				'label' => __('Options'),
				'type' => 'text',
				'help' => __('List the payment options offered by your payment provider, or provide generic text. This will go in the sentence "To pay online with ____, click ...".'),
			],
		]);
		echo $this->element('Settings/input', [
			'category' => 'registration',
			'name' => 'online_payment_text',
			'options' => [
				'label' => __('Text of Online Payment Directions'),
				'type' => 'textarea',
				'help' => __('Customize any text to add to the default online payment directions.'),
				'class' => 'wysiwyg_simple',
			],
		]);
	}

	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'popup',
		'options' => [
			'label' => __('Popup'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Handle online payments in a popup window?'),
		],
	]);

	if (!$affiliate) {
		echo $this->element('Settings/input', [
			'category' => 'payment',
			'name' => 'invoice_implementation',
			'options' => [
				'label' => __('Invoice Implementation'),
				'type' => 'select',
				'options' => Configure::read('options.invoice'),
				'empty' => $empty,
				'hide_single' => true,
			],
		]);
		echo $this->element('Settings/input', [
			'category' => 'payment',
			'name' => 'reg_id_format',
			'options' => [
				'label' => __('Event ID Format String'),
				'help' => __('sprintf format string for the event ID, sent to the payment processor as the item number.'),
			],
		]);
	}

	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'test_payments',
		'options' => [
			'label' => __('Test Payments'),
			'type' => 'radio',
			'options' => Configure::read('options.test_payment'),
			'help' => __('Who should get test instead of live payments? If set to admins, then admins are the only ones who will get the online payment option.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'currency',
		'options' => [
			'label' => __('Currency'),
			'type' => 'radio',
			'options' => Configure::read('options.currency'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'tax1_enable',
		'options' => [
			'label' => __('Tax1 Enable'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Enable first tax'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'tax1_name',
		'options' => [
			'label' => __('First Tax Name'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'tax2_enable',
		'options' => [
			'label' => __('Tax2 Enable'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Enable second tax'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'payment',
		'name' => 'tax2_name',
		'options' => [
			'label' => __('Second Tax Name'),
		],
	]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
