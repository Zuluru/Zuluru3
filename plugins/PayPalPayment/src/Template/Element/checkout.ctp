<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 * @var \App\Model\Entity\Credit[] $debits
 * @var \App\Model\Entity\Person $person
 * @var \PayPalPayment\Event\Listener $listener
 * @var int $number_of_providers
 * @var bool $is_test
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

// JavaScript for no address bar
$this->Html->scriptBlock('
function open_payment_window_paypal() {
	window.open("", "payment_window_paypal", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
	var a = window.setTimeout("document.paypal_form.submit();", 500);
}
', ['block' => true, 'buffer' => true]);

$invnum = sprintf(Configure::read('registration.order_id_format'), $registrations[0]->id);
if (!empty($registrations[0]->payments)) {
	$invnum .= '-' . (count($registrations[0]->payments) + 1);
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

$fields = [
	'RETURNURL' => Router::url(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index'], true),
	'CANCELURL' => Router::url(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index'], true),
	'SOLUTIONTYPE' => 'Sole',
	'REQCONFIRMSHIPPING' => 0,
	'NOSHIPPING' => 1,
	'EMAIL' => $email,
	'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
	'PAYMENTREQUEST_0_SHIPTONAME' => "{$person->first_name} {$person->last_name}",
	'PAYMENTREQUEST_0_SHIPTOSTREET' => $person->addr_street,
	'PAYMENTREQUEST_0_SHIPTOCITY' => $person->addr_city,
	'PAYMENTREQUEST_0_SHIPTOSTATE' => $person->addr_prov,
	'PAYMENTREQUEST_0_SHIPTOZIP' => $person->addr_postalcode,
	'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => $person->addr_country,
	'PAYMENTREQUEST_0_SHIPTOPHONENUM' => $person->home_phone,
	'PAYMENTREQUEST_0_INVNUM' => $invnum,
	'PAYMENTREQUEST_0_CURRENCYCODE' => Configure::read('payment.currency'),
];

$total_amount = $total_tax = 0;
$ids = [];
$m = 0;
foreach ($registrations as $registration) {
	[$cost, $tax1, $tax2] = $registration->paymentAmounts();

	$fields["L_PAYMENTREQUEST_0_NAME$m"] = $registration->event->name;
	$fields["L_PAYMENTREQUEST_0_DESC$m"] = substr($registration->event->payment_desc, 0, 127);
	$fields["L_PAYMENTREQUEST_0_AMT$m"] = sprintf('%.2f', $cost);
	$fields["L_PAYMENTREQUEST_0_TAXAMT$m"] = sprintf('%.2f', $tax1 + $tax2);
	$fields["L_PAYMENTREQUEST_0_NUMBER$m"] = sprintf(Configure::read('payment.reg_id_format'), $registration->event->id);
	$fields["L_PAYMENTREQUEST_0_QTY$m"] = 1;

	$total_amount += $cost + $tax1 + $tax2;
	$total_tax += $tax1 + $tax2;
	$ids[] = $registration->id;
	++ $m;
}
foreach ($debits as $debit) {
	$fields["L_PAYMENTREQUEST_0_NAME$m"] = $debit->notes;
	$fields["L_PAYMENTREQUEST_0_DESC$m"] = substr($debit->notes, 0, 127);
	$fields["L_PAYMENTREQUEST_0_AMT$m"] = sprintf('%.2f', -$debit->balance);
	$fields["L_PAYMENTREQUEST_0_NUMBER$m"] = sprintf(Configure::read('registration.debit_id_format'), $debit->id);
	$fields["L_PAYMENTREQUEST_0_QTY$m"] = 1;

	$total_amount -= $debit->balance;
	$ids[] = "D{$debit->id}";
	++ $m;
}
$fields['PAYMENTREQUEST_0_CUSTOM'] = $person->id . ':' . implode(',', $ids);
$fields['PAYMENTREQUEST_0_AMT'] = sprintf('%.2f', $total_amount);
$fields['PAYMENTREQUEST_0_ITEMAMT'] = sprintf('%.2f', $total_amount - $total_tax);
if ($total_tax > 0) {
	$fields['PAYMENTREQUEST_0_TAXAMT'] = $total_tax;
}

$response = $listener->getAPI($is_test)->SetExpressCheckout($fields);
if (is_array($response)) {
	// Build the online payment form
	if ($is_test) {
		$paypal_url = 'https://www.sandbox.paypal.com/';
	} else {
		$paypal_url = 'https://www.paypal.com/';
	}
	$url = "{$paypal_url}webscr?cmd=_express-checkout&token=" . urlencode($response['TOKEN']);
	$form_options = ['url' => $url, 'name' => 'paypal_form', 'escape' => false];
	$submit_options = ['div' => false, 'alt' => 'Pay now'];
	if (Configure::read('payment.popup')) {
		$form_options['target'] = 'payment_window_paypal';
		$submit_options['onClick'] = 'open_payment_window_paypal();';
	}
	echo $this->Form->create(false, $form_options);
	echo $this->Form->submit('https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif', $submit_options);
	echo $this->Form->end();
} else {
	echo $this->Html->scriptBlock("alert('$response');");
}
