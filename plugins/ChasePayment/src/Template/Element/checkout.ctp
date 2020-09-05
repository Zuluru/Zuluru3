<?php
/**
 * @type \App\View\AppView $this
 * @type \App\Model\Entity\Registration[] $registrations
 * @type \App\Model\Entity\Person $person
 * @type int $number_of_providers
 * @type \ChasePayment\Http\API $api
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

if (function_exists ('mhash')) {

	// Use mhash function to compute the hash.
	function hmac($key, $data) {
		return (bin2hex (mhash(MHASH_MD5, $data, $key)));
	}

} else {

	function hmac($key, $data) {
		// RFC 2104 HMAC implementation for php to compute the MD5 HMAC.
		// Credit: Lance Rushing - http://www.php.net/manual/en/function.mhash.php

		$b = 64; // byte length for MD5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

}

// JavaScript for no address bar
$this->Html->scriptBlock('
function open_payment_window() {
	window.open("", "payment_window", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
	var a = window.setTimeout("document.payment_form.submit();", 500);
}
', ['block' => true, 'buffer' => true]);

$order_fmt = Configure::read('registration.order_id_format');

// Generate a unique order id
$time = FrozenTime::now()->toUnixString();
$invoice_num = sprintf($order_fmt, $registrations[0]->id);
$unique_order_num = $invoice_num . sprintf('-%010d', $time);

// Build the online payment form
if ($api->isTest()) {
	$login = Configure::read('payment.chase_test_store');
	$key = Configure::read('payment.chase_test_password');
	$server = 'rpm.demo';
} else {
	$login = Configure::read('payment.chase_live_store');
	$key = Configure::read('payment.chase_live_password');
	$server = 'checkout';
}

$form_options = ['url' => "https://$server.e-xact.com/payment", 'name' => 'payment_form'];
$submit_options = ['div' => false];

if (Configure::read('payment.popup')) {
	$form_options['target'] = 'payment_window';
	$submit_options['onClick'] = 'open_payment_window();';
}

echo $this->Form->create(false, $form_options);

function quick_hidden($ths, $name, $value) {
	return $ths->Form->hidden($name, ['name' => $name, 'value' => $value]);
}

echo quick_hidden($this, 'x_login', $login);
echo quick_hidden($this, 'x_test_request', $api->isTest() ? 'TRUE' : 'FALSE');
echo quick_hidden($this, 'x_fp_sequence', $unique_order_num);
echo quick_hidden($this, 'x_fp_timestamp', $time);
echo quick_hidden($this, 'x_show_form', 'PAYMENT_FORM');
echo quick_hidden($this, 'x_type', 'AUTH_CAPTURE');
echo quick_hidden($this, 'x_receipt_link_method', 'GET');
echo quick_hidden($this, 'x_relay_response', 'TRUE');

$join = '<|>';
$currency = Configure::read('payment.currency');
$total_amount = $total_tax = 0;
$ids = $event_ids = [];
foreach ($registrations as $registration) {
	[$cost, $tax1, $tax2] = $registration->paymentAmounts();

	echo quick_hidden($this, 'x_line_item', implode($join, [
		sprintf(Configure::read('payment.reg_id_format'), $registration->event->id),
		$registration->event->name,
		$registration->event->payment_desc,
		'1',
		$cost,
		($tax1 + $tax2 > 0) ? 'YES' : 'NO',
	]) . $join);
	$total_amount += $cost + $tax1 + $tax2;
	$total_tax += $tax1 + $tax2;
	$ids[] = $registration->id;
	$event_ids[] = $registration->event->id;
}
$total_amount = sprintf('%.2f', $total_amount);
$hash_source = implode('^', [
	$login,
	$unique_order_num,
	$time,
	$total_amount,
	$currency,
]);
echo quick_hidden($this, 'x_fp_hash', hmac($key, $hash_source));
echo quick_hidden($this, 'x_description', implode(',', $ids));
echo quick_hidden($this, 'x_reference_3', implode (',', $event_ids));

echo quick_hidden($this, 'x_cust_id', $person->id);

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
echo quick_hidden($this, 'x_email', $email);

echo quick_hidden($this, 'x_invoice_num', $invoice_num);
echo quick_hidden($this, 'x_currency_code', $currency);
echo quick_hidden($this, 'x_amount', $total_amount);
if ($total_tax > 0) {
	echo quick_hidden($this, 'x_tax', $total_tax);
}
echo quick_hidden($this, 'x_first_name', $person->first_name);
echo quick_hidden($this, 'x_last_name', $person->last_name);
echo quick_hidden($this, 'x_address', $person->addr_street);
echo quick_hidden($this, 'x_city', $person->addr_city);
echo quick_hidden($this, 'x_state', $person->addr_prov);
echo quick_hidden($this, 'x_zip', $person->addr_postalcode);
echo quick_hidden($this, 'x_country', $person->addr_country);
echo quick_hidden($this, 'x_phone', $person->home_phone);

echo $this->Form->submit(__n('Pay', 'Pay with Chase', $number_of_providers), $submit_options);

echo $this->Form->end();
