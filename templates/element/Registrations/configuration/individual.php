<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event|null $event
 * @var string[] $affiliates
 */

use Cake\ORM\TableRegistry;

// Get the list of divisions
$conditions = [
	'Divisions.close > NOW()',
	'Leagues.affiliate_id IN' => array_keys($affiliates),
];
if ($event && $event->division_id) {
	$conditions = ['OR' => [
		$conditions,
		['Divisions.id' => $event->division_id],
	]];
}
$divisions = TableRegistry::getTableLocator()->get('Divisions')->find()
	->contain('Leagues')
	->where($conditions)
	->toArray();

echo $this->Form->control('division_id', [
	'label' => __('Division'),
	'options' => collection($divisions)->combine('id', 'full_league_name')->toArray(),
	'empty' => __('Not associated with any division'),
	'help' => __('This is only used internally to improve event/division linkage.'),
	'secure' => false,
]);
if ($this->Form->hasFormProtector()) {
	$this->Form->unlockField('division_id');
}

echo $this->Form->control('level_of_play', [
	'size' => 70,
	'help' => __('Indicate the expected level(s) of play in this division.'),
	'secure' => false,
]);
if ($this->Form->hasFormProtector()) {
	$this->Form->unlockField('membership_begins');
}
