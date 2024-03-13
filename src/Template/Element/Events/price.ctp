<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var int $index
 */

use Cake\Core\Configure;
if ($event->has('prices') && array_key_exists($index, $event->prices)) {
	$errors = $event->prices[$index]->getErrors();
	$new = false;
} else {
	$new = true;
}
$collapsed = (empty($errors) && !$event->isNew());
?>

<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="PriceHeading<?= $index ?>">
		<h4 class="panel-title"><a role="button" class="accordion-toggle<?= $collapsed ? ' collapsed' : '' ?>" data-toggle="collapse" data-parent="#accordion" href="#PriceDetails<?= $index ?>" aria-expanded="<?= $collapsed ? 'true' : 'false' ?>" aria-controls="PriceDetails<?= $index ?>"><?= __('Price Point Details') ?>:</a>
			<?= $this->Form->input("prices.$index.name", [
				'placeholder' => __('Price Point Name'),
			]) ?>
		</h4>
	</div>
	<div id="PriceDetails<?= $index ?>" class="panel-collapse collapse<?= $collapsed ? '' : ' in' ?>" role="tabpanel" aria-labelledby="PriceHeading<?= $index ?>">
		<div class="panel-body">
<?php
if (!$new) {
	echo $this->Form->input("prices.$index.id");
}
echo $this->Form->input("prices.$index.description", [
	'cols' => 70,
	'rows' => 5,
	'help' => __('Complete description of the price point, HTML is allowed, may be blank if it\'s self-explanatory or if you only have one price for the event.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->input("prices.$index.cost", [
	'help' => __('Cost of this event, may be 0, <span class="warning-message">not including tax</span>.') . ($event->isNew() ? '' : ' ' . __('If you change the price, anyone who has registered for this but not yet paid will still be charged their original registration price, not the new price. If you need to charge them the new price, close this price point (via the "Closes on" field below), make sure that "Allow Late Payment" is disabled, and add a new price point with the new price.')),
]);
if (Configure::read('payment.tax1_enable')) {
	echo $this->Form->input("prices.$index.tax1", [
		'label' => Configure::read('payment.tax1_name'),
	]);
}
if (Configure::read('payment.tax2_enable')) {
	echo $this->Form->input("prices.$index.tax2", [
		'label' => Configure::read('payment.tax2_name'),
	]);
}
echo $this->Form->input("prices.$index.open", [
	'label' => __('Opens on'),
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$event->isNew(),
	// TODO: JavaScript link on "12:01AM" to set the time in the inputs
	'help' => __('The date and time at which registration for this event will open ({0} recommended to disambiguate noon from midnight).', __('12:01AM')),
]);
echo $this->Form->input("prices.$index.close", [
	'label' => __('Closes on'),
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$event->isNew(),
	// TODO: JavaScript link on "11:59PM" to set the time in the inputs
	'help' => __('The date and time at which registration for this event will close ({0} recommended to disambiguate midnight from noon).', __('11:59PM')),
]);
echo $this->Form->input("prices.$index.register_rule", [
	'cols' => 70,
	'help' => __('Rules that must be passed to allow a person to register for this event.') .
		' ' . $this->Html->help(['action' => 'rules', 'rules']),
]);
echo $this->Form->input("prices.$index.allow_late_payment", [
	'options' => Configure::read('options.enable'),
	'default' => false,
	'help' => __('This should generally be enabled except for "early bird" specials that expire and are followed by a higher-priced alternative, or if you absolutely require that payment be made by the registration cutoff date.'),
]);
if (Configure::read('registration.online_payments')) {
	echo $this->Jquery->toggleInput("prices.$index.online_payment_option", [
		'type' => 'select',
		'options' => Configure::read('options.online_payment'),
		'default' => false,
	], [
		'values' => [
			ONLINE_MINIMUM_DEPOSIT => ".minimum-deposit-$index",
			ONLINE_SPECIFIC_DEPOSIT => ".specific-deposit-$index",
			ONLINE_DEPOSIT_ONLY => ".deposit-only-$index",
		],
		'parent_selector' => '.form-group',
	]);
	echo $this->Form->input("prices.$index.minimum_deposit", [
		'class' => "minimum-deposit-$index",
		'default' => 0,
		'help' => __('Minimum allowable deposit that the registrant must make.'),
		'secure' => false,
	]);
	echo $this->Form->input("prices.$index.minimum_deposit", [
		'label' => __('Deposit Amount'),
		'class' => "specific-deposit-$index",
		'default' => 0,
		'secure' => false,
	]);
	echo $this->Form->input("prices.$index.minimum_deposit", [
		'label' => __('Deposit Amount'),
		'class' => "deposit-only-$index",
		'default' => 0,
		'secure' => false,
	]);
}
echo $this->Jquery->toggleInput("prices.$index.allow_reservations", [
	'options' => Configure::read('options.enable'),
	'default' => false,
], [
	'selector' => ".reservation-$index",
	'parent_selector' => '.form-group',
]);
echo $this->Form->input("prices.$index.reservation_duration", [
	'class' => "reservation-$index",
	'default' => 0,
	'help' => __('If enabled above, the time in minutes that a reservation will be held before reverting to "Unpaid" status. One day = 1440 minutes.'),
	'secure' => false,
]);
?>
		</div>
	</div>
</div>
