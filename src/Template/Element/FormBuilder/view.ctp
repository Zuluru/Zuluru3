<?php
foreach ($questions as $question => $details) {
	if (!array_key_exists('restricted', $details) || !$details['restricted'] || $show_restricted) {
		echo $this->Html->tag('h3', $details['text']);
		if (array_key_exists('options', $details)) {
			$options = $details['options'];
		}
		if ($answers->has($question)) {
			$answer = $answers->$question;
		} else {
			$answer = null;
		}
		echo $this->element("FormBuilder/view/{$details['type']}", compact('options', 'answer'));
	}
}
