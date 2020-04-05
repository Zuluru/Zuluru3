<?php
if (!isset($secure)) {
	$secure = true;
}
foreach ($questions as $question => $details) {
	$field = "$prefix.$question";
	$options = (array_key_exists('options', $details) ? $details['options'] : []);
	$desc = (array_key_exists('desc', $details) ? $details['desc'] : null);
	$label = $details['text'];
	echo $this->Html->tag('div',
		$this->element("FormBuilder/input/{$details['type']}", compact('field', 'label', 'options', 'desc', 'preview', 'secure')),
		['class' => 'input required']);
}
