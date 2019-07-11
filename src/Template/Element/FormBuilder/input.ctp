<?php
if (!isset($secure)) {
	$secure = true;
}
foreach ($questions as $question => $details) {
	$field = "$prefix.$question";
	if (array_key_exists('options', $details)) {
		$options = $details['options'];
	}
	if (array_key_exists('desc', $details)) {
		$desc = $details['desc'];
	}
	$label = $details['text'];
	echo $this->Html->tag('div',
		$this->element("FormBuilder/input/{$details['type']}", compact('field', 'label', 'options', 'desc', 'preview', 'secure')),
		['class' => 'input required']);
}
