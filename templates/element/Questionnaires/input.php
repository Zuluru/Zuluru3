<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

if ($questionnaire->has('questions')) {
	foreach ($questionnaire->questions as $key => $question) {
		// Anonymous questions are not included when editing an existing registration
		if (!isset($edit) || empty($question->anonymous)) {
			echo $this->element('Questions/input', compact('key', 'question'));
		}
	}
}
