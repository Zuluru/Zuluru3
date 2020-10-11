<?php
/**
 * @type \App\View\AppView $this
 * @type \App\Model\Entity\Registration[] $registrations
 * @type \App\Model\Entity\Person $person
 * @type \BamboraPayment\Event\Listener $listener
 * @type int $number_of_providers
 * @type bool $is_test
 */

use Cake\Core\Configure;

// JavaScript for no address bar
$this->Html->scriptBlock('
function open_payment_window_bambora() {
	window.open(url, "payment_window_bambora", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
}
', ['block' => true, 'buffer' => true]);

$order_fmt = Configure::read('registration.order_id_format');
$invoice_num = sprintf($order_fmt, $registrations[0]->id);

// Build the online payment form
if ($is_test) {
	$merchant_id = Configure::read('payment.bambora_test_merchant_id');
	$hash_key = Configure::read('payment.bambora_test_hash_key');
} else {
	$merchant_id = Configure::read('payment.bambora_live_merchant_id');
	$hash_key = Configure::read('payment.bambora_live_hash_key');
}

$total_amount = 0;
$ids = $event_ids = [];
foreach ($registrations as $registration) {
	[$cost, $tax1, $tax2] = $registration->paymentAmounts();
	$total_amount += $cost + $tax1 + $tax2;
	$ids[] = $registration->id;
	$event_ids[] = $registration->event->id;
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

$query_string = http_build_query([
	'merchant_id' => $merchant_id,
	'trnAmount' => $total_amount,
	'trnOrderNumber' => $invoice_num,
	//'trnLanguage' => TODO: eng or fre
	'ordName' => $person->full_name,
	'ordEmailAddress' => $email,
	'ordAddress1' => $person->addr_street,
	'ordCity' => $person->addr_city,
	//'ordProvince' => $person->addr_prov, TODO: Two letter code
	'ordPostalCode' => $person->addr_postalcode,
	//'ordCountry' => $person->addr_country, TODO: Two letter code
	'ref1' => implode(',', $ids),
	'ref2' => implode (',', $event_ids),
	'ref3' => $person->id,
]);
$query_string .= '&hashValue=' . sha1($query_string . $hash_key);
$url = "https://web.na.bambora.com/scripts/payment/payment.asp?{$query_string}";

$link_options = [];
if (Configure::read('payment.popup')) {
	$link_options = [
		'target' => 'payment_window_bambora',
		'onClick' => 'open_payment_window_bambora();',
	];
}

echo $this->Html->tag('div',
	$this->Html->link(__n('Pay', 'Pay with Bambora', $number_of_providers), $url, $link_options),
	['class' => 'btn btn-default']
);
