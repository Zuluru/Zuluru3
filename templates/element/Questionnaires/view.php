<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

use Cake\Utility\Hash;

$rows = [];
foreach ($questionnaire->questions as $question) {
	if (empty($question->anonymous)) {
		switch ($question->type) {
			case 'radio':
			case 'select':
			case 'text':
			case 'textarea':
				$answer = collection($responses)->firstMatch(['question_id' => $question->id]);
				if ($answer) {
					if ($question->type == 'select' && $question->has('options') && !Hash::numeric(array_keys($question->options))) {
						$answer = $answer->answer_text;
					} else if ($question->type == 'select' || $question->type == 'radio') {
						if ($question->has('answers')) {
							$answer = collection($question->answers)->firstMatch(['id' => $answer->answer_id]);
							if ($answer) {
								$answer = $answer->answer;
							}
						} else if ($question->has('options') && $answer && array_key_exists($answer->answer_id, $question->options)) {
							$answer = $question->options[$answer->answer_id];
						} else {
							// This shouldn't happen, unless questionnaires change after a registration happened
							$answer = null;
						}
					} else {
						$answer = $answer->answer_text;
					}
					$name = $question->question;
					if ($question->has('name') && !empty($question->name)) {
						$name = $question->name;
					}
					$rows[] = [$name, $answer];
				}
				break;

			case 'checkbox':
				$label = $question->question;
				if ($question->has('name') && !empty($question->name)) {
					$label = $question->name;
				}
				$answers = collection($responses)->match(['question_id' => $question->id])->extract('answer_id')->toList();
				// Deal with both checkbox groups and single checkboxes
				if (!empty($question->answers) && !empty($answers)) {
					$answers = collection($question->answers)->filter(function ($answer) use ($answers) {
						return in_array($answer->id, $answers);
					})->extract('answer')->toArray();
					foreach ($answers as $answer) {
						$rows[] = [$label, $answer];
						$label = '';
					}
				} else {
					$rows[] = [$label, empty($answers) ? __('N/A') : ($answers[0] ? __('Yes') : __('No'))];
				}
				break;

			// TODO: Handle these
			case 'group_start':
			case 'group_end':
				break;

			case 'description':
			case 'label':
				$rows[] = [[$question->question, ['colspan' => 2]]];
				break;
		}
	}
}

echo $this->Html->tag('table', $this->Html->tableCells($rows), ['class' => 'list']);
