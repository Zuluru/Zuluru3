<?php
use Cake\Core\Configure;

foreach ($questions as $question => $details) {
	// We can view the answer if it's an unrestricted question, we're admin, or we're
	// coordinator.  $is_coordinator will only be set in places where we're looking at
	// something to do with a league, like game results.
	if (!array_key_exists('restricted', $details) || !$details['restricted'] || Configure::read('Perm.is_admin') ||
		(isset($is_coordinator) && $is_coordinator))
	{
		echo $this->Html->tag('h3', __($details['text']));
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
