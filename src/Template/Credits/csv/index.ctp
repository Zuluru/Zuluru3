<?php
/**
 * @var \App\Model\Entity\Credit[] $credits
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var bool $all
 */

$fp = fopen('php://output','w+');

$header = [
	__('Person'),
	__('Email'),
	__('Alternate Email'),
	__('Date'),
	__('Initial Amount'),
	__('Amount Used'),
	__('Notes'),
];

fputcsv($fp, $header);

$affiliate_id = null;
foreach ($credits as $credit) {
	if (count($affiliates) > 1 && $credit->affiliate_id != $affiliate_id) {
		$affiliate_id = $credit->affiliate_id;
		fputcsv($fp, [h($credit->affiliate->name)]);
	}

	$row = [
		$credit->person->full_name,
		$credit->person->email,
		$credit->person->alternate_email,
		$credit->created,
		$credit->amount,
		$credit->amount_used,
		$credit->notes,
	];
	fputcsv($fp, $row);
}

fclose($fp);
