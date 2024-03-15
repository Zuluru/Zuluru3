<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\League $league
 */

use App\Model\Table\TeamsTable;
use Cake\Core\Configure;
use App\Controller\AppController;

$fp = fopen('php://output','w+');

$fields = [
	__('Division') => count($league->divisions) > 1,
	__('Team') => true,
	__('User ID') => true,
	__('Role') => true,
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
	Configure::read('gender.column') => Configure::read('gender.label'),
	'birthdate' => __('Birthdate'),
	'height' => __('Height'),
	'skill_level' => ['name' => __('Skill Level'), 'model' => 'skills'],
	'shirt_size' => __('Shirt Size'),
	'alternate_first_name' => __('Alternate First Name'),
	'alternate_last_name' => __('Alternate Last Name'),
	'alternate_work_phone' => __('Alternate Work Phone'),
	'alternate_work_ext' => __('Alternate Work Ext'),
	'alternate_mobile_phone' => __('Alternate Mobile Phone'),
	__('Added') => true,
];

[$header1, $header2, $player_fields, $contact_fields] = \App\Lib\csvFields(collection($league->divisions)->extract("teams.{*}.people.{*}"), $fields, true);
if (!empty($header1)) {
	fputcsv($fp, $header1);
}
fputcsv($fp, $header2);

foreach ($league->divisions as $division) {
	foreach ($division->teams as $team) {
		usort($team->people, [TeamsTable::class, 'compareRoster']);
		foreach ($team->people as $person) {
			$role = Configure::read("options.roster_role.{$person->_joinData->role}");
			switch ($person->_joinData->status) {
				case ROSTER_INVITED:
					$role .= __(' ({0})', __('invited'));
					break;

				case ROSTER_REQUESTED:
					$role .= __(' ({0})', __('requested'));
					break;
			}

			$row = [
				$team->name,
				$person->id,
				$role,
			];
			foreach ($player_fields as $field => $name) {
				if (is_array($name)) {
					$model = $name['model'];
					if (is_array($person->$model) && !empty($person->{$model}[0]) && $person->{$model}[0]->has($field)) {
						$row[] = $person->{$model}[0]->$field;
					} else if (is_a($person->$model, \Cake\ORM\Entity::class) && $person->$model->has($field)) {
						$row[] = $person->$model->$field;
					} else {
						$row[] = '';
					}
				} else {
					if ($person->has($field)) {
						$row[] = $person->$field;
					} else {
						$row[] = '';
					}
				}
			}
			$row[] = $person->_joinData->created;
			if (count($league->divisions) > 1) {
				array_unshift($row, $division->name);
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

			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
