<?php
/**
 * @var \App\Model\Entity\League $league
 * @var \App\Module\Spirit $spirit_obj
 * @var \App\Model\Entity\SpiritEntry $entry
 * @var int $value
 * @var string $question
 * @var bool $show_spirit_scores
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
