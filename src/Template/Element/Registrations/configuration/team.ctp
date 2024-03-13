<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

// Get the list of divisions
$divisions = TableRegistry::getTableLocator()->get('Divisions')->find()
	->contain('Leagues')
	->where([
		'Divisions.close > NOW()',
		'Leagues.affiliate_id IN' => array_keys($affiliates),
	])
	->toArray();

echo $this->Form->input('division_id', [
	'label' => __('Division'),
	'options' => collection($divisions)->combine('id', 'full_league_name')->toArray(),
	'empty' => 'Create no team records',
	'help' => __('Registrations performed through this event will create team records in this division.'),
	'secure' => false,
]);
$this->Form->unlockField('division_id');

echo $this->Form->input('level_of_play', [
	'size' => 70,
	'help' => __('Indicate the expected level(s) of play in this division.'),
	'secure' => false,
]);
$this->Form->unlockField('level_of_play');

echo $this->Form->input('ask_status', [
	'label' => __('Team status'),
	'type' => 'checkbox',
	'help' => __('Ask whether team rosters will be open or closed during registration?'),
	'secure' => false,
]);
$this->Form->unlockField('ask_status');

if (Configure::read('feature.region_preference')) {
	echo $this->Form->input('ask_region', [
		'label' => __('Region Preference'),
		'type' => 'checkbox',
		'help' => __('Ask teams for their regional preference during registration?'),
		'secure' => false,
	]);
	$this->Form->unlockField('ask_region');
}

echo $this->Form->input('ask_attendance', [
	'label' => __('Attendance Tracking'),
	'type' => 'checkbox',
	'help' => __('Ask teams whether they want to use attendance tracking during registration?'),
	'secure' => false,
]);
$this->Form->unlockField('ask_attendance');
