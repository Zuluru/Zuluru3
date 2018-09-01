<?php
$rows = [];
foreach ($event->questionnaire->questions as $question) {
	if (in_array($question->type, ['select', 'radio', 'checkbox'])) {
		// There's no way to summarize answers to auto questions
		// TODO: Revisit once regional preference, etc. are handled
		if ($question->id > 0) {
			$title = $question->name;
			foreach ($question->answers as $answer) {
				$count = collection($responses)->firstMatch(['question_id' => $question->id, 'answer_id' => $answer->id]);
				$rows[] = [$title, $answer->answer, $count ? $count->count : 0];
				$title = '';
			}
		}
	} else if ($question->type == 'label') {
		$rows[] = [[$question->question, ['colspan' => 3]]];
	}
}

if (!empty($rows))
	echo $this->Html->tag('table', $this->Html->tableCells($rows), ['class' => 'list']);
