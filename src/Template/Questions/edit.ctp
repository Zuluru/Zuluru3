<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Question'));
$this->Html->addCrumb(__('Edit'));
?>

<div class="questions form">
	<?= $this->Form->create($question, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Edit Question') ?></legend>
<?php
echo $this->Form->input('name', [
	'size' => 60,
	'help' => __('A short name for this question, to be used as a heading in administrative reports.'),
]);
echo $this->Form->input('question', [
	'cols' => 60,
	'help' => __('The full text of the question, to be shown to users.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->input('type', [
	'options' => Configure::read('options.question_types'),
	'empty' => true,
]);
echo $this->Form->input('anonymous', [
	'label' => __('Anonymous results'),
	'help' => __('Will responses to this question be kept anonymous?'),
]);

if ($question->type == 'radio' || $question->type == 'select' || $question->type = 'checkbox'):
?>
		<table id="Answers" class="sortable list">
			<thead>
				<tr>
					<th><?= __('Answer') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$i = 0;
	foreach ($question->answers as $answer) {
		++$i;
		echo $this->element('Questions/edit_answer', compact('answer', 'i'));
	}
?>

			</tbody>
		</table>

		<div class="actions columns">
			<ul class="nav nav-pills">
<?php
	echo $this->Html->tag('li', $this->Jquery->ajaxLink(__('Add an answer to this question'), [
		'url' => ['action' => 'add_answer', 'question' => $question->id],
		'disposition' => 'append',
		'selector' => '#Answers > tbody',
	]));
?>
			</ul>
		</div>
<?php
endif;
?>

	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
