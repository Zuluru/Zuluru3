<?php
/**
 * @type \App\View\AppView $this
 * @type \App\Model\Entity\Registration[] $registrations
 * @type \App\Model\Entity\Person $person
 * @type \StripePayment\Event\Listener $listener
 * @type int $number_of_providers
 * @type bool $is_test
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

if (Configure::read('payment.popup')) {
	echo $this->Html->scriptBlock("alert('Stripe integration does not support popup payment windows at this time.\nA system administrator will need to disable the popup option.');");
	return;
}

if (!empty($person->email)) {
	$email = $person->email;
} else if (!empty($person->alternate_email)) {
	$email = $person->alternate_email;
} else {
	foreach ($person->related as $related) {
		if (!empty($related->email)) {
			$email = $related->email;
			break;
		} else if (!empty($related->alternate_email)) {
			$email = $related->alternate_email;
			break;
		}
	}
}

$items = [];
foreach ($registrations as $registration) {
	[$cost, $tax1, $tax2] = $registration->paymentAmounts();

	$items[] = [
		'price_data' => [
			'currency' => strtolower(Configure::read('payment.currency')),
			'product_data' => [
				'name' => $registration->event->name,
				'description' => $registration->event->payment_desc,
				'metadata' => ['event_id' => $registration->event->id, 'registration_id' => $registration->id],
			],
			'unit_amount' => round(($cost + $tax1 + $tax2) * 100),
		],
		'quantity' => 1,
	];
}

try {
	$session = $listener->getAPI($is_test)->checkoutSessionCreate([
		'client_reference_id' => $registrations[0]->id,
		'customer_email' => $email,
		'payment_method_types' => ['card'],
		'line_items' => $items,
		'mode' => 'payment',
		'success_url' => Router::url(['plugin' => 'StripePayment', 'controller' => 'Payment', 'action' => 'success'], true),
		'cancel_url' => Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'checkout'], true),
	]);

	if ($session->getLastResponse() && $session->getLastResponse()->code == 200) {
		if ($is_test) {
			$login = Configure::read('payment.stripe_test_publishable_key');
		} else {
			$login = Configure::read('payment.stripe_live_publishable_key');
		}

		$this->Html->script('https://js.stripe.com/v3/', ['block' => true]);
		$this->Html->scriptBlock("
var stripe = Stripe('$login');
function open_payment_window_stripe() {
	stripe.redirectToCheckout({
		sessionId: '$session->id'
	}).then(function (result) {
		alert(result.error.message);
	});
}
", ['block' => true]);
		?>
		<div id="payment-request-button"></div>
		<?php
		// Build the online payment form
		$form_options = ['url' => '#', 'name' => 'stripe_form', 'escape' => false];
		$submit_options = ['div' => false, 'alt' => 'Pay', 'onClick' => 'open_payment_window_stripe();'];
		echo $this->Form->create(false, $form_options);
		echo $this->Form->submit(__n('Pay', 'Pay with Stripe', $number_of_providers), $submit_options);
		echo $this->Form->end();
	} else {
		echo $this->Html->scriptBlock("alert('$session');", ['block' => true, 'buffer' => true]);
	}
} catch (\Stripe\Exception\ApiErrorException $ex) {
	$this->Html->scriptBlock('alert("' . $ex->getMessage() . '");', ['block' => true, 'buffer' => true]);
}
