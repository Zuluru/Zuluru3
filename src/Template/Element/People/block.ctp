<?php
use Cake\Core\Configure;

if (!$person) {
	echo __('Unknown');
	return;
}

$id = "people_person_{$person->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (!isset($display_field)) {
	$display_field = 'full_name';
}

if (!Configure::read('Perm.is_logged_in') && in_array($person->status, ['locked', 'inactive'])) {
	echo $person->$display_field;
	return;
} else if (!isset($link) || $link) {
	echo $this->Html->link($person->$display_field,
		['controller' => 'People', 'action' => 'view', 'person' => $person->id],
		$options);
} else {
	echo $this->Html->tag('span', $person->$display_field, $options);
}
