<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Question'));
$this->Breadcrumbs->add(__('Edit'));
?>

<div class="questions form">
	<?= $this->Form->create($question, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Edit Question') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'size' => 60,
	'help' => __('A short name for this question, to be used as a heading in administrative reports.'),
]);
echo $this->Form->i18nControls('question', [
	'cols' => 60,
	'help' => __('The full text of the question, to be shown to users.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->control('type', [
	'options' => Configure::read('options.question_types'),
	'empty' => true,
]);
echo $this->Form->control('anonymous', [
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
<?php
	if (in_array($question->type, ['select', 'radio'])) {
		echo $this->Bootstrap->navPills([
			$this->Jquery->ajaxLink(
				__('Add an answer to this question'),
				[
					'url' => ['action' => 'add_answer', '?' => ['question' => $question->id]],
					'disposition' => 'append',
					'selector' => '#Answers > tbody',
				],
				['class' => $this->Bootstrap->navPillLinkClasses()]
			),
		]);
	}
?>
		</div>
<?php
endif;
?>

	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
