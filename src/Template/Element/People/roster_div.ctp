<?php
// This is required on every page where a roster role or position change popup is used
use Cake\Core\Configure;

foreach (array_keys(Configure::read('options.sport')) as $sport) {
	echo $this->Jquery->inPlaceWidgetOptions(Configure::read("sports.{$sport}.positions"), [
		'type' => "{$sport}_roster_position",
		'url-param' => 'position',
		'ajax' => true,
	]);
}

echo $this->Jquery->inPlaceWidgetOptions(Configure::read('options.roster_role'), [
	'type' => 'roster_role',
	'url-param' => 'role',
	'ajax' => true,
	'confirm' => __('Are you sure? Not all roster changes are reversible.'),
]);
