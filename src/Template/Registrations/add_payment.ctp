<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Payment $payment
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb($registration->person->full_name);
$this->Html->addCrumb($registration->event->name);
$this->Html->addCrumb(__('Add Payment'));
?>

<div class="registrations form">
<h2><?= __('Add Payment') ?></h2>
<?= $this->Form->create($payment, ['align' => 'horizontal']) ?>

	<fieldset>
		<legend><?= __('Payment Details') ?></legend>
<?php
echo $this->Form->hidden('registration_id', [
	'value' => $registration->id,
]);
echo $this->Form->control('payment_amount', [
	'default' => $registration->balance,
]);

$options = Configure::read('options.payment_method');
$credit_options = [];
foreach ($registration->person->credits as $credit) {
	if ($credit->affiliate_id == $registration->event->affiliate_id) {
		$credit_options[$credit->id] = $this->Number->currency($credit->balance);
		if ($credit->amount_used > 0) {
			$credit_options[$credit->id] .= __(' ({0} - {1})', $this->Number->currency($credit->amount), $this->Number->currency($credit->amount_used));
		}
	}
}
if (empty($credit_options)) {
	unset($options['Credit Redeemed']);
}

// All options show the standard inputs...
$selectors = array_fill_keys(array_keys($options), '#standard_options');
// ...except Credit Redeemed
$selectors['Credit Redeemed'] = '#credit_options';
echo $this->Jquery->toggleInput('payment_method', [
	'options' => $options,
	'empty' => __('Select one:'),
], [
	'values' => $selectors,
]);
?>

		<div id="standard_options">
<?php
echo $this->Form->control('notes', [
	'type' => 'textarea',
	'cols' => 72,
	'help' => __('These notes will be attached to the new payment record, and are only visible to admins.'),
	'secure' => false,
]);
?>

		</div>
		<div id="credit_options">
<?php
echo $this->Form->control('credit_id', [
	'options' => $credit_options,
	'help' => __('The lowest of the credit amount, specified payment amount, and outstanding balance will be used as the actual payment amount.'),
	'secure' => false,
]);
?>

		</div>
	</fieldset>

<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
		<?= $this->element('Registrations/actions', ['registration' => $registration, 'format' => 'list']) ?>
	</ul>
</div>
