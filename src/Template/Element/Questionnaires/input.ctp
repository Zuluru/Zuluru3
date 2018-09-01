<?php
if ($questionnaire->has('questions')) {
	foreach ($questionnaire->questions as $key => $question) {
		// Anonymous questions are not included when editing an existing registration
		if (!isset($edit) || empty($question->anonymous)) {
			echo $this->element('Questions/input', compact('key', 'question'));
		}
	}
}
