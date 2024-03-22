<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

?>
<table id="Questions" class="sortable list">
	<thead>
		<tr>
			<th><?= __('Question') ?></th>
			<th><?= __('Required') ?></th>
			<th><?= __('Actions') ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$i = 0;

foreach ($questionnaire->questions as $question) {
	echo $this->element('Questionnaires/edit_question', compact('questionnaire', 'question', 'i'));
	++$i;
}
?>

	</tbody>
</table>

<div class="zuluru_dialog" id="AddQuestionDiv" title="Add Question">
<p><?= __('Type part of the question you want') ?></p>
<?= $this->Jquery->autocompleteInput('Add.question', 'AddQuestion', [
	'url' => ['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $questionnaire->affiliate_id]],
	'disposition' => 'ajax_add_row',
	'add_url' => ['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' => $questionnaire->id, 'question' => '__id__']],
	'add_selector' => '#Questions',
]) ?>

</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Add an existing question to this questionnaire'), '#', [
	'onclick' => 'return addQuestion();'
]));
?>
	</ul>
</div>

<?php
// Prepare the dialog
$cancel = __('Cancel');
$this->Html->scriptBlock("
	zjQuery('#AddQuestionDiv').dialog({
		autoOpen: false,
		buttons: { '$cancel': function () { zjQuery(this).dialog('close'); } },
		modal: true,
		resizable: false,
		width: 500
	});
", ['buffer' => true]);
