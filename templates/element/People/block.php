<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;

if (!$person) {
	echo __('Unknown');
	return;
}

$id = "people_person_{$person->id}";

$new_options = ['id' => $id];
if ($this->Identity->isLoggedIn()) {
	$new_options['class'] = 'trigger';
}

if (isset($options)) {
	$options = array_merge($new_options, $options);
} else {
	$options = $new_options;
}
if (!isset($display_field)) {
	$display_field = 'full_name';
}

if (!$this->Identity->isLoggedIn() && in_array($person->status, ['locked', 'inactive'])) {
	echo $person->$display_field;
	return;
} else if (!isset($link) || $link) {
	echo $this->Html->link($person->$display_field,
		['controller' => 'People', 'action' => 'view', '?' => ['person' => $person->id]],
		$options);
} else {
	echo $this->Html->tag('span', $person->$display_field, $options);
}
