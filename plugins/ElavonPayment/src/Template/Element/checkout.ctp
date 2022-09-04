<?php
/**
 * @type \App\View\AppView $this
 * @type \App\Model\Entity\Registration[] $registrations
 * @type \App\Model\Entity\Person $person
 * @type \ElavonPayment\Event\Listener $listener
 * @type int $number_of_providers
 * @type bool $is_test
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

// JavaScript for no address bar
$this->Html->scriptBlock('
function open_payment_window_elavon() {
	window.open(url, "payment_window_elavon", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
}
', ['block' => true, 'buffer' => true]);

$order_fmt = Configure::read('registration.order_id_format');
$invoice_num = sprintf($order_fmt, $registrations[0]->id);

// Build the online payment form
$total_amount = 0;
$ids = $items = [];
foreach ($registrations as $registration) {
	[$cost, $tax1, $tax2] = $registration->paymentAmounts();
	$total_amount += $cost + $tax1 + $tax2;
	$ids[] = $registration->id;

	$items[] = implode('::', [
		sprintf('%.2f', $cost + $tax1 + $tax2),
		1,
		$registration->event->name,
		'',
	]);
}
$total_amount = sprintf('%.2f', $total_amount);

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

$response = $listener->getAPI($is_test)->checkoutSessionCreate([
	'ssl_transaction_type' => 'ccsale',
	'ssl_amount' => $total_amount,
	'ssl_customer_id' => $person->id,
	'ssl_first_name' => $person->first_name,
	'ssl_last_name' => $person->last_name,
	'ssl_email' => $email,
	'ssl_merchant_txn_id' => $registrations[0]->id,
	'ssl_invoice_number' => $invoice_num,
	'ssl_description' => implode(',', $ids),
	'ssl_product_string' => implode('; ', $items),
	'ssl_salestax_indicator' => 'Y',
	'ssl_callback_url' => Router::url(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index'], true),
]);
if (is_array($response)) {
	if ($is_test) {
		$endpoint = 'https://api.demo.convergepay.com/hosted-payments';
	} else {
		$endpoint = 'https://api.convergepay.com/hosted-payments';
	}
	$endpoint .= '?ssl_txn_auth_token=' . urlencode($response['token']);

	$link_options = [];
	if (Configure::read('payment.popup')) {
		$link_options = [
			'target' => 'payment_window_elavon',
			'onClick' => 'open_payment_window_elavon();',
		];
	}

	echo $this->Html->tag('div',
		$this->Html->link(__n('Pay now', 'Pay with Elavon', $number_of_providers), $endpoint, $link_options),
		['class' => 'btn btn-default']
	);
} else {
	echo $this->Html->scriptBlock("alert('$response');");
}
