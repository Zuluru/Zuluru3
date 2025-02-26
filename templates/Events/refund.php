<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Registration[] $registrations
 * @var \App\Model\Entity\Payment $refund
 * @var int $id
 * @var int $price_id
 */

$this->Breadcrumbs->add(__('Events'));
$this->Breadcrumbs->add($event->name);
if ($price_id) {
	$this->Breadcrumbs->add(current($event->prices)->name);
}
$this->Breadcrumbs->add(__('Refund'));
?>

<div class="events refund">
	<h2><?php
		echo __('Event Refund') . ': ' . $event->name;
		if ($price_id) {
			echo ': ' . current($event->prices)->name;
		}
	?></h2>

	<?= $this->Form->create($refund, ['align' => 'horizontal']) ?>

	<fieldset>
		<legend class="border-bottom"><?= __('Refund Details') ?></legend>
<?php
echo $this->Jquery->toggleInput('payment_type', [
	'label' => __('Refund Method'),
	'options' => [
		'Refund' => __('Refund'),
		'Credit' => __('Credit'),
	],
], [
	'values' => [
		'Refund' => '.refund_input',
		'Credit' => '.credit_input',
	],
]);
echo $this->Form->hidden('payment_method', [
	'value' => 'Other',
]);
echo $this->Jquery->toggleInput('amount_type', [
	'options' => [
		'total' => __('Total Amount Paid'),
		'prorated' => __('Pro-rated'),
		'input' => __('Specific Amount'),
	],
	'empty' => '---',
	'help' => $this->Html->tag('span', __('People will get back whatever amount they have paid (less any refunds they may already have received).'), ['class' => 'total'])
], [
	'values' => [
		'total' => '.total',
		'prorated' => '.prorated',
		'input' => '.input',
	],
]);
echo $this->Html->tag('div',
	$this->Form->control('payment_percent', [
		'label' => __('Refund Percent'),
		'default' => 100,
		'help' => __('People will get back this percentage of their expected total, to a maximum of whatever they have paid. For people who have already paid a pro-rated amount, or received a discount or partial refund, you should probably NOT select them in the list, and deal with them on a one-off basis.'),
		'secure' => false,
	]),
	['class' => 'prorated']
);
echo $this->Html->tag('div',
	$this->Form->control('payment_amount', [
		'label' => __('Refund Amount'),
		'default' => current($event->prices)->total,
		'help' => __('People will get back this exact amount, to a maximum of whatever they have paid. For people who have already paid a pro-rated amount, or received a discount or partial refund, you should probably NOT select them in the list, and deal with them on a one-off basis.'),
		'secure' => false,
	]),
	['class' => 'input']
);

echo $this->Html->tag('div',
	$this->Form->control('online_refund', [
		'label' => __('Issue applicable refunds through online payment provider? It will be your responsibility to manually refund any payments that might have been collected offline.'),
		'type' => 'checkbox',
		'checked' => true,
	]),
	['class' => 'refund_input']
);

echo $this->Form->control('mark_refunded', [
	'label' => __('Mark these registrations as refunded?'),
	'type' => 'checkbox',
	'checked' => false,
	'help' => $event->event_type->type == 'team' ? __('Remember that marking team registrations as refunded will entirely remove teams from the system.') : false,
]);

echo $this->Form->control('notes', [
	'type' => 'textarea',
	'cols' => 72,
	'help' => __('These notes will be preserved with the original registration, and are only visible to admins.'),
]);

echo $this->Html->tag('div',
	$this->Form->control('credit_notes', [
		'type' => 'textarea',
		'cols' => 72,
		'default' => __('Credit for registration for {0}', $event->name),
		'help' => __('These notes will be attached to the new credit record, and will be visible by the person in question.'),
		'secure' => false,
	]),
	['class' => 'credit_input']
);
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Registrations') ?></legend>
		<div id="RegistrationList" class="zuluru_pagination">

<?= $this->element('Events/refunds', compact('event', 'registrations')) ?>

		</div>
	</fieldset>

<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
