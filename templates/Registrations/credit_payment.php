<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Payment $payment
 * @var \App\Model\Entity\Payment $refund
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Registration'));
$this->Breadcrumbs->add($registration->person->full_name);
$this->Breadcrumbs->add($registration->event->name);
$this->Breadcrumbs->add(__('Credit Payment'));
?>

<div class="registrations form">
	<h2><?= __('Credit Payment') ?></h2>
	<?= $this->Form->create($refund, ['align' => 'horizontal']) ?>

	<fieldset>
		<legend><?= __('Credit Details') ?></legend>
<?php
echo $this->Form->hidden('registration_id', [
	'value' => $registration->id,
]);
echo $this->Form->hidden('payment_type', [
	'value' => 'Credit',
]);
echo $this->Form->hidden('payment_method', [
	'value' => 'Other',
]);
echo $this->Form->control('payment_amount', [
	'label' => __('Credit Amount'),
	'default' => ($refund->getErrors() || $registration->getErrors()) ? -$refund->amount : $payment->paid,
]);

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

echo $this->Form->control('credit_notes', [
	'type' => 'textarea',
	'cols' => 72,
	'default' => __('Credit for registration for {0}', $registration->event->name),
	'help' => __('These notes will be attached to the new credit record, and will be visible by the person in question.'),
]);
?>
	</fieldset>

<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
