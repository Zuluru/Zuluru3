<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 * @var \App\Model\Entity\Credit[] $debits
 * @var \App\Model\Entity\Person $person
 * @var \ElavonPayment\Event\Listener $listener
 * @var int $number_of_providers
 * @var bool $is_test
 */

use App\Exception\PaymentException;
use Cake\Core\Configure;
use Cake\Routing\Router;

// JavaScript for no address bar
$this->Html->scriptBlock('
function open_payment_window_elavon() {
	window.open(url, "payment_window_elavon", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
}
', ['block' => true, 'buffer' => true]);

$order_fmt = Configure::read('registration.order_id_format');
$debit_fmt = Configure::read('registration.debit_id_format');
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
foreach ($debits as $debit) {
    $total_amount -= $debit->balance;
    $ids[] = "D{$debit->id}";

    $items[] = implode('::', [
        sprintf('%.2f', -$debit->balance),
        1,
        $debit->notes,
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

try {
	$token = $listener->getAPI($is_test)->checkoutSessionCreate([
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
	]);

	if ($is_test) {
		$endpoint = 'https://api.demo.convergepay.com/hosted-payments';
	} else {
		$endpoint = 'https://api.convergepay.com/hosted-payments';
	}
	$endpoint .= '?ssl_txn_auth_token=' . urlencode($token);

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
} catch (PaymentException $ex) {
	echo $this->Html->scriptBlock('alert("' . $ex->getMessage() . '");');
}
