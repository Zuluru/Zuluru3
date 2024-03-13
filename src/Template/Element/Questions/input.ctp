<?php
use Cake\Utility\Hash;

/**
 * @var \App\Model\Entity\Question $question
 * @var int $key
 */

// There may be multiple checkboxes with the same key, but we need separate indices for them
$key *= 100;

if ($question->has('question')) {
	$options = [
		'label' => [
			'text' => $question->question,
			'escape' => false,
		],
		'required' => !empty($question->required) || !empty($question->_joinData->required),
		'type' => $question->type,
	];
	if ($question->has('help')) {
		$options['help'] = $question->help;
	}
}
$options['escape'] = false;

switch ($question->type) {
	case 'radio':
		$options['label'] = $options['legend'] = false;
		$options['options'] = collection($question->answers)->combine('id', 'answer')->toArray();
		if ($question->has('default')) {
			$options['default'] = $question->default;
		}
		$item = $this->Html->tag('fieldset',
			$this->Html->tag('legend', $question->question) .
			$this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
				$this->Form->input("responses.$key.answer_id", $options));
		break;

	case 'select':
		if ($question->has('options')) {
			// TODO: Make this more like the entities read from the database?
			$options['options'] = $question->options;
		} else {
			$options['options'] = collection($question->answers)->combine('id', 'answer')->toArray();
		}
		$options['empty'] = '---';
		if ($question->has('default')) {
			$options['default'] = $question->default;
		}
		if (Hash::numeric(array_keys($options['options']))) {
			$answer = 'answer_id';
		} else {
			$answer = 'answer_text';
		}
		$item = $this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
			$this->Form->input("responses.$key.$answer", $options);
		break;

	case 'checkbox':
		// Deal with both checkbox groups and single checkboxes
		if (empty($question->answers)) {
			if (!empty($question->default)) {
				$options['checked'] = true;
			}
			$item = $this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
				$this->Form->input("responses.$key.answer_id", $options);
		} else if (count($question->answers) > 1) {
			$item = $this->Html->tag('label', $question->question);
			foreach ($question->answers as $akey => $answer) {
				$options['label'] = $answer->answer;
				$options['value'] = $answer->id;
				$item .= $this->Form->hidden('responses.' . ($key + $akey) . '.question_id', ['value' => $question->id]) .
					$this->Form->input('responses.' . ($key + $akey) . '.answer_id', $options);
			}
		} else {
			$answer = current($question->answers);
			$options['label'] = $answer->answer;
			$options['value'] = $answer->id;
			$item = $this->Html->tag('label', $question->question) .
				$this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
				$this->Form->input("responses.$key.answer_id", $options);
		}
		break;

	case 'text':
		$options['size'] = 75;
		$item = $this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
			$this->Form->input("responses.$key.answer_text", $options);
		break;

	case 'textarea':
		$options['cols'] = 72;
		$item = $this->Form->hidden("responses.$key.question_id", ['value' => $question->id]) .
			$this->Form->input("responses.$key.answer_text", $options);
		break;

	case 'group_start':
		$item = "<fieldset><legend>{$question->question}</legend>\n";
		break;

	case 'group_end':
		$item = "</fieldset>\n";
		break;

	case 'description':
		$item = $this->Html->tag('label', $question->question);
		break;

	case 'label':
		$item = $this->Html->tag('label', $question->question);
		break;
}

echo $item;
