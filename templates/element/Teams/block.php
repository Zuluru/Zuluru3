<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

use Cake\Core\Configure;

$id = "teams_team_{$team->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}

if (Configure::read('feature.team_logo') && !empty($team->logo) && (!isset($show_shirt) || $show_shirt)) {
	echo $this->Html->image($team->logo) . ' ';
}
echo $this->Html->link($team->name,
	['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
	$options);
if (Configure::read('feature.shirt_colour') && isset($team->shirt_colour) && (!isset($show_shirt) || $show_shirt)) {
	echo ' ' . $this->element('shirt', ['colour' => $team->shirt_colour]);
}
