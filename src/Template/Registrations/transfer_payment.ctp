<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb($registration->person->full_name);
$this->Html->addCrumb($registration->event->name);
$this->Html->addCrumb(__('Transfer Payment'));
?>

<div class="registrations form">
	<h2><?= __('Transfer Payment') ?></h2>
	<?= $this->Form->create($payment, ['align' => 'horizontal']) ?>

	<fieldset>
		<legend><?= __('Transfer Details') ?></legend>
<?php
echo $this->Form->hidden('registration_id', [
	'value' => $registration->id,
]);
echo $this->Form->hidden('payment_type', [
	'value' => 'Transfer',
]);
echo $this->Form->hidden('payment_method', [
	'value' => 'Other',
]);

echo $this->Form->input('payment_amount', [
	'label' => __('Transfer Amount'),
	'default' => $payment->paid,
	'help' => __('The lowest of the specified transfer amount, unrefunded amount from this payment, and outstanding balance on the target registration will be used as the actual transfer amount.'),
]);

if (!in_array($registration->payment, Configure::read('registration_cancelled'))) {
	echo $this->Form->input('mark_refunded', [
		'label' => __('Mark this registration as refunded?'),
		'type' => 'checkbox',
		'checked' => true,
	]);
} else {
	echo $this->Form->hidden('mark_refunded', ['value' => 0]);
}

// Turn the list of unpaid registrations into a useful list of select options
$options = collection($unpaid)->combine('id', function ($entity) { return $entity->event->translateField('name'); }, 'person_id')->toArray();
if (count($options) == 1 && array_key_exists($registration->person_id, $options)) {
	$options = current($options);
} else {
	// Include the person's name as a grouping level, if there is anything registered to anyone else.
	foreach ($options as $key => $val) {
		unset($options[$key]);
		$options[$this->UserCache->read('Person.full_name', $key)] = $val;
	}
}

echo $this->Form->input('transfer_to_registration_id', [
	'label' => __('Registration to transfer this payment to'),
	'options' => $options,
	'empty' => __('Select one:'),
]);

// TODO: Set the default value here based on selection of registration above.
echo $this->Form->input('notes', [
	'type' => 'textarea',
	'label' => __('Transfer From Notes'),
	'cols' => 72,
	'help' => 'These notes will be attached to the new payment record on the original registration, and are only visible to admins.',
]);

echo $this->Form->input('transfer_to_notes', [
	'type' => 'textarea',
	'cols' => 72,
	'default' => __('Transferred from registration #{0} for {1}', $registration->id, $registration->event->name),
	'help' => 'These notes will be attached to the new payment record on the new registration, and are only visible to admins.',
]);
?>
	</fieldset>

<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
