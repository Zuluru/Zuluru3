<?php
/**
 * @type \App\Model\Entity\Division $division
 */

use Cake\ORM\TableRegistry;

$id = "divisions_division_{$division->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($link_text)) {
	if (!isset($field)) {
		$field = 'league_name';
	}
	$link_text = $division->$field;
}

if (!isset($url)) {
	if (TableRegistry::get('Divisions')->find('byLeague', ['league' => $division->league_id])->count() == 1) {
		$url = ['controller' => ($division->schedule_type == 'tournament' ? 'Tournaments' : 'Leagues'), 'action' => 'view', 'league' => $division->league_id];
	} else {
		$url = ['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id];
	}
}
echo $this->Html->link($link_text, $url, $options);
