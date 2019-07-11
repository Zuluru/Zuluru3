<?php
use App\Controller\AppController;

if (!isset($i)) {
	$i = mt_rand(1000, mt_getrandmax());
}
?>
<tr>
	<td class="handle"><?php
		echo $this->Form->hidden("questions.$i.id", ['value' => $question->id]);
		echo $this->Form->hidden("questions.$i._joinData.sort");
		echo $question->question . __(' ({0})', $question->type) .
			($question->anonymous ? __(' ({0})', __('anonymous')) : '');
	?></td>
	<td><?php
		echo $this->Form->input("questions.$i._joinData.required", [
			'div' => false,
			'label' => false,
			'type' => 'checkbox',
		]);
	?></td>
	<td class="actions"><?php
		echo $this->Html->link(__('Edit'), ['controller' => 'Questions', 'action' => 'edit', 'question' => $question->id, 'return' => AppController::_return()]);
		echo $this->Jquery->ajaxLink(__('Remove'), [
			'url' => ['action' => 'remove_question', 'questionnaire' => $questionnaire->id, 'question' => $question->id],
			'disposition' => 'remove_closest',
			'selector' => 'tr',
		]);

		if ($question->active) {
			echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['controller' => 'Questions', 'action' => 'deactivate', 'question' => $question->id]]);
		} else {
			echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['controller' => 'Questions', 'action' => 'activate', 'question' => $question->id]]);
		}
	?></td>
</tr>
