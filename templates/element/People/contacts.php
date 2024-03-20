<?php
/**
 * @var \App\Model\Entity\Person $person
 */

$lines = [];
$visible_properties = $person->getVisible();

if (in_array('email', $visible_properties) && !empty($person->email)) {
	$lines[] = $this->Html->link($person->email, "mailto:{$person->email}");
}
if (in_array('alternate_email', $visible_properties) && !empty($person->alternate_email)) {
	$lines[] = $this->Html->link($person->alternate_email, "mailto:{$person->alternate_email}");
}
if (in_array('home_phone', $visible_properties) && !empty($person->home_phone)) {
	$lines[] = $person->home_phone . __(' ({0})', __('home'));
}
if (in_array('work_phone', $visible_properties) && !empty($person->work_phone)) {
	$line = $person->work_phone;
	if (!empty($person->work_ext)) {
		$line .= ' x' . $person->work_ext;
	}
	$line .= __(' ({0})', __('work'));
	$lines[] = $line;
}
if (in_array('mobile_phone', $visible_properties) && !empty($person->mobile_phone)) {
	$lines[] = $person->mobile_phone . __(' ({0})', __('mobile'));
}

echo implode($this->Html->tag('br'), $lines);

$links = [];
if ($this->Authorize->can('vcf', $person) && !empty($lines)) {
	$links[] = $this->Html->link(__('VCF'), ['action' => 'vcf', '?' => ['person' => $person->id]]);
}
if ($this->Authorize->can('note', $person)) {
	$links[] = $this->Html->link(__('Add Note'), ['action' => 'note', '?' => ['person' => $person->id]]);
}
if ($this->Authorize->can('act_as', $person)) {
	$links[] = $this->Html->link(__('Act As'), ['action' => 'act_as', '?' => ['person' => $person->id]]);
}
if (!empty($links)) {
	echo $this->Html->tag('br') . implode(' / ', $links);
}
