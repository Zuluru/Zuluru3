<?php
use Cake\Core\Configure;

if ((!isset($entry) || $entry === false) && (!isset($value) || $value === null)) {
	return;
}

if (!isset($question)) {
	$question = null;
}
$max = $spirit_obj->max($question);

if (!isset($value) || $value === null) {
	if ($question === null) {
		if ($entry->entered_sotg === null || !$league->numeric_sotg) {
			$value = $spirit_obj->calculate($entry);
		} else {
			$value = $entry->entered_sotg;
		}
	} else {
		$value = $entry->$question;
	}
}
$ratio = $value / $max;
if ($max < 0) {
	$ratio = 1 - $ratio;
}
$file = $spirit_obj->symbol($ratio);
echo $this->Html->iconImg("spirit_$file.png");

switch ($league->display_sotg) {
	case 'symbols_only':
		if (Configure::read('Perm.is_admin')) {
			printf(' (%.2f)', $value);
		}
		break;

	case 'coordinator_only':
		if (Configure::read('Perm.is_admin') || $is_coordinator) {
			printf(' (%.2f)', $value);
		}
		break;

	case 'numeric':
	case 'all':
		printf(' (%.2f)', $value);
		break;
}
