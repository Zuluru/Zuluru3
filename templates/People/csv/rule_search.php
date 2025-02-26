<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $people
 */

use Cake\Core\Configure;
use App\Controller\AppController;

$fp = fopen('php://output','w+');

$fields = [
	__('User ID') => true,
	'first_name' => Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name'),
	'last_name' => __('Last Name'),
	'email' => __('Email Address'),
	'alternate_email' => __('Alternate Email Address'),
	'addr_street' => __('Address'),
	'addr_city' => __('City'),
	'addr_prov' => __('Province'),
	'addr_postalcode' => __('Postal Code'),
	'home_phone' => __('Home Phone'),
	'work_phone' => __('Work Phone'),
	'work_ext' => __('Work Ext'),
	'mobile_phone' => __('Mobile Phone'),
	'gender' => __('Gender'),
	'roster_designation' => Configure::read('gender.label'),
	'birthdate' => __('Birthdate'),
	'height' => __('Height'),
	'shirt_size' => __('Shirt Size'),
	'alternate_first_name' => __('Alternate First Name'),
	'alternate_last_name' => __('Alternate Last Name'),
	'alternate_work_phone' => __('Alternate Work Phone'),
	'alternate_work_ext' => __('Alternate Work Ext'),
	'alternate_mobile_phone' => __('Alternate Mobile Phone'),
];

list($header1, $header2, $player_fields, $contact_fields) = \App\Lib\csvFields(collection($people), $fields, true);
if (!empty($header1)) {
	fputcsv($fp, $header1);
}
fputcsv($fp, $header2);

foreach ($people as $person) {
	$row = [
		$person->id,
	];

	foreach ($player_fields as $field => $name) {
		if ($person->has($field)) {
			$row[] = $person->$field;
		} else {
			$row[] = '';
		}
	}

	if (!empty($contact_fields) && (empty($person->user_id) || AppController::_isChild($person))) {
		foreach ($person->related as $i => $relative) {
			foreach (array_keys($contact_fields[$i]) as $field) {
				if ($relative->has($field)) {
					$row[] = $relative->$field;
				} else {
					$row[] = '';
				}
			}
		}
	}

	// Output the data row
	fputcsv($fp, $row);
}

fclose($fp);
