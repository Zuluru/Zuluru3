<?php
/**
 * @type \App\Model\Entity\Credit[] $credits
 * @type \App\Model\Entity\Affiliate[] $affiliates
 * @type boolean $all
 */

$fp = fopen('php://output','w+');

$header = [
	__('Person'),
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
		$credit->created,
		$credit->amount,
		$credit->amount_used,
		$credit->notes,
	];
	fputcsv($fp, $row);
}

fclose($fp);
