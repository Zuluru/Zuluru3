<?php
/**
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Payment $payment
 * @var \App\Http\API $api
 * @var \App\Model\Entity\Payment $refund
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb($registration->person->full_name);
$this->Html->addCrumb($registration->event->name);
$this->Html->addCrumb(__('Refund Payment'));
?>

<div class="registrations form">
	<h2><?= __('Refund Payment') ?></h2>
	<?= $this->Form->create($refund, ['align' => 'horizontal']) ?>

	<fieldset>
		<legend><?= __('Refund Details') ?></legend>
<?php
echo $this->Form->hidden('registration_id', [
	'value' => $registration->id,
]);
echo $this->Form->hidden('payment_type', [
	'value' => 'Refund',
]);
echo $this->Form->hidden('payment_method', [
	'value' => 'Other',
]);
echo $this->Form->control('payment_amount', [
	'label' => __('Refund Amount'),
	'default' => ($refund->getErrors() || $registration->getErrors()) ? -$refund->payment_amount : $payment->paid,
]);

if (empty($payment->registration_audit_id)) {
	echo $this->Html->para('warning-message', __('This payment was recorded manually, so in addition to noting the refund here you will need to issue a refund manually.'));
} else if ($api && $api->canRefund($payment)) {
	echo $this->Form->control('online_refund', [
		'label' => __('Issue refund through online payment provider?'),
		'type' => 'checkbox',
		'checked' => true,
	]);
} else {
	echo $this->Html->para('warning-message', __('Note that your online payment provider does not currently support automatic refunds, so in addition to noting the refund here you will need to issue a refund manually. You may be able to enable online refunds in the payment provider plugin settings.'));
}

if (!in_array($registration->getOriginal('payment'), Configure::read('registration_cancelled'))) {
	echo $this->Form->control('mark_refunded', [
		'label' => __('Mark this registration as refunded?'),
		'type' => 'checkbox',
		'checked' => true,
	]);
} else {
	echo $this->Form->hidden('mark_refunded', ['value' => 0]);
}

echo $this->Form->control('notes', [
	'type' => 'textarea',
	'cols' => 72,
	'help' => __('These notes will be preserved with the original registration, and are only visible to admins.'),
]);
?>
	</fieldset>

<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
