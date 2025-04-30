<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question[] $questions
 * @var \App\Model\Entity\SpiritEntry $answers
 * @var bool $show_restricted
 */

$preview = false;
foreach ($questions as $question => $details) {
	if (!array_key_exists('restricted', $details) || !$details['restricted'] || $show_restricted) {
		echo $this->Html->tag('h5', $details['text']);
		if (array_key_exists('options', $details)) {
			$options = $details['options'];
		}
		if ($answers->has($question)) {
			$answer = $answers->$question;
		} else {
			$answer = null;
		}
		echo $this->element("FormBuilder/view/{$details['type']}", compact('preview', 'options', 'answer'));
	}
}
