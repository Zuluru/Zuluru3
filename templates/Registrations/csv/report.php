<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 */

use App\Model\Entity\Payment;
use Cake\Core\Configure;

$fp = fopen('php://output','w+');
$header = [
	__('Created Date'),
	__('Order ID'),
	__('Event ID'),
	__('Event'),
	__('Price Point'),
	__('User ID'),
	Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name'),
	__('Last Name'),
	__('Payment Status'),
	__('Total Amount'),
	__('Amount Paid'),
];
if (Configure::read('registration.online_payments')) {
	$header[] = __('Transaction ID');
}
$header[] = __('Notes');
if (count($affiliates) > 1 && empty($affiliate)) {
	array_unshift($header, __('Affiliate'));
}
fputcsv($fp, $header);

$order_fmt = Configure::read('registration.order_id_format');

foreach ($registrations as $registration) {
	$order_id = sprintf($order_fmt, $registration->id);

	$data = [
		$registration->created,
		$order_id,
		$registration->event->id,
		$registration->event->name,
		$registration->price->name,
		$registration->person->id,
		$registration->person->first_name,
		$registration->person->last_name,
		$registration->payment,
		$registration->total_amount,
		$registration->total_payment,
	];
	if (Configure::read('registration.online_payments')) {
		$data[] = implode(';', array_unique(collection($registration->payments)->extract('registration_audit.transaction_id')->toArray()));
	}

	$notes = [];
	if ($registration->notes) {
		$notes[] = $registration->notes;
	}
	$payment_notes = collection($registration->payments)->filter(function (Payment $payment) { return $payment->notes; })->extract('notes')->toArray();
	if ($payment_notes) {
		$notes = array_unique(array_merge($notes, $payment_notes));
	}
	$data[] = implode('; ', $notes);

	if (count($affiliates) > 1 && empty($affiliate)) {
		array_unshift($data, $registration->event->affiliate->name);
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
