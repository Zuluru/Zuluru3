<?php
/**
 * @type $team \App\Model\Entity\Team
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use App\Controller\AppController;

$fp = fopen('php://output','w+');

$roster = collection($team->teams_people ?? []);
$has_numbers = Configure::read('feature.shirt_numbers') && $roster->some(function (\App\Model\Entity\TeamsPerson $roster_entry) {
	return $roster_entry->number != null;
});
$positions = $team->division_id ? Configure::read("sports.{$team->division->league->sport}.positions") : [];

$fields = [
	__('Number') => $has_numbers,
	'first_name' => Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name'),
	'last_name' => __('Last Name'),
	__('Role') => true,
	__('Position') => !empty($positions),
	Configure::read('gender.label') => $this->Authorize->can('display_gender', new ContextResource($team, ['division' => $team->division])) && Configure::read('offerings.genders') !== 'Open',
	__('Date Joined') => true,
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
	'alternate_first_name' => __('Alternate First Name'),
	'alternate_last_name' => __('Alternate Last Name'),
	'alternate_work_phone' => __('Alternate Work Phone'),
	'alternate_work_ext' => __('Alternate Work Ext'),
	'alternate_mobile_phone' => __('Alternate Mobile Phone'),
];

$identity = $this->Authorize->getIdentity();
list($header1, $header2, $player_fields, $contact_fields) = \App\Lib\csvFields($roster->extract('person'), $fields, $identity->isManagerOf($team->division->league));
if (!empty($header1)) {
	fputcsv($fp, $header1);
}
fputcsv($fp, $header2);

// Player first and last names go at the beginning of the row, not in the middle with the rest of the player_fields
unset($player_fields['first_name']);
unset($player_fields['last_name']);

$column = Configure::read('gender.column');

foreach ($team->teams_people as $roster_entry) {
	$person = $roster_entry->person;

	$row = [
		$person->first_name,
		$person->last_name,
		$roster_entry->role,
	];
	if ($has_numbers) {
		array_unshift($row, $roster_entry->number);
	}
	if (!empty($positions)) {
		$row[] = $roster_entry->position;
	}
	$row[] = $person->$column;
	$row[] = $this->Time->date($roster_entry->created);

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
