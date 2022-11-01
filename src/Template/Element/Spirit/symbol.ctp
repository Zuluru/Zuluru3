<?php
/**
 * @type $league \App\Model\Entity\League
 * @type $spirit_obj \App\Module\Spirit
 * @type $entry \App\Model\Entity\SpiritEntry
 * @type $value int
 * @type $question string
 * @type $show_spirit_scores bool
 */

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

if ($show_spirit_scores) {
	printf(' (%.2f)', $value);
}
