<?php
// Sometimes, there will be a 'League' key, sometimes not
if (array_key_exists('League', $league)) {
	$league = array_merge($league, $league);
	unset($league);
}
$id = "leagues_league_{$league['id']}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($field)) {
	$field = 'full_name';
}
if (!isset($name)) {
	$name = $league[$field];
}
if (!isset($tournaments)) {
	$tournaments = false;
}
echo $this->Html->link($name, ['controller' => ($tournaments ? 'Tournaments' : 'Leagues'), 'action' => 'view', ($tournaments ? 'tournament' : 'league') => $league['id']], $options);
