<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb($registration->person->full_name);
$this->Html->addCrumb($registration->event->name);
$this->Html->addCrumb(__('Edit'));
?>

<div class="registrations form">
	<h2><?= __('Edit Registration') ?></h2>
	<?= $this->Form->create($registration, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Registration Details') ?></legend>
<?php
echo $this->Form->input('order', [
	'label' => __('Order ID'),
	'value' => sprintf(Configure::read('registration.order_id_format'), $registration->id),
	'disabled' => true,
]);
if ($this->Authorize->getIdentity()->isManagerOf($registration->event)) {
	echo $this->Form->input('name', [
		'value' => $registration->person->full_name,
		'disabled' => true,
		'size' => 75,
	]);
}
echo $this->Form->input('event', [
	'value' => $registration->event->name,
	'disabled' => true,
	'size' => 75,
]);
if ($this->Authorize->getIdentity()->isManagerOf($registration->event)) {
	echo $this->Jquery->toggleInput('payment', [
		'options' => Configure::read('options.payment'),
		'help' => $this->Html->para('warning-message', __('Change this only in extreme circumstances; for proper accounting, refunds and payments should be entered through links on the registration view page.')),
	], [
		'values' => [
			'Reserved' => '.reserved',
		],
		'parent_selector' => '.form-group',
	]);
	echo $this->Form->input('reservation_expires', [
		'templates' => [
			'inputContainer' => '<div class="form-group reserved {{type}}{{required}}">{{content}}</div>',
			'inputContainerError' => '<div class="form-group reserved {{type}}{{required}} has-error">{{content}}</div>',
		],
		'secure' => false,
	]);
	echo $this->Form->input('notes', [
		'type' => 'textarea',
		'cols' => 72,
	]);
}
?>
	</fieldset>

<?php
if (!empty($registration->event->questionnaire)):
	// TODOBOOTSTRAP: Can we make this part of the form not 'align' => 'horizontal', and then turn it on again for the payment section?
?>
	<fieldset><legend><?= __('Registration Answers') ?></legend>
		<div class="related">
			<?= $this->element('Questionnaires/input', ['questionnaire' => $registration->event->questionnaire, 'responses' => $registration->responses, 'edit' => true]) ?>

		</div>
	</fieldset>
<?php
endif;

// If there is more than one price option, or if deposits are allowed and either they are not the only option or the amount is not fixed,
// then we need to provide payment options.
if (count($registration->event->prices) > 1 || ($registration->event->prices[0]['allow_deposit'] && (!$registration->event->prices[0]['deposit_only'] || !$registration->event->prices[0]['fixed_deposit']))):
?>
	<fieldset>
		<legend><?= __('Payment') ?></legend>
<?php
	if (count($registration->event->prices) > 1):
?>
		<p><?= __('This event has the following options. Please select your preference. Note that options may have limitations, which will be noted after selection.') ?></p>
<?php
		$options = [];
		foreach ($registration->event->prices as $price_option) {
			$options[$price_option->id] = $price_option->name . __(' ({0})', $this->Number->currency($price_option->total));
		}
		echo $this->Jquery->ajaxInput('price_id', [
			'selector' => '#PaymentDetails',
			'url' => ['action' => 'register_payment_fields', 'registration_id' => $registration->id, 'for_edit' => true],
		], [
			'label' => __('Registration Options'),
			'empty' => __('Select one:'),
			'options' => $options,
		]);
	else:
		// There is only one price option
		echo $this->Form->hidden('price_id', ['value' => $registration->price_id]);
	endif;
?>
		<div id="PaymentDetails">
			<?= $this->element('Registrations/register_payment_fields', ['price' => $registration->price, 'registration' => $registration, 'for_edit' => true]) ?>
		</div>
	</fieldset>
<?php
else:
	// There is only one price option
	echo $this->Form->hidden('price_id', ['value' => $registration->price_id]);
endif;
?>

	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
