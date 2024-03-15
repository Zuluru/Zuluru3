<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Answer $answer
 */

if (!isset($i)) {
	$i = mt_rand(1000, mt_getrandmax());
}
?>
<tr>
	<td class="handle"><?php
	echo $this->Form->hidden("answers.$i.id", ['value' => $answer->id]);
	echo $this->Form->hidden("answers.$i.sort", ['value' => $answer->sort]);
	echo $this->Form->control("answers.$i.answer", [
		'div' => false,
		'label' => false,
		'type' => 'text',
		'size' => 60,
		'value' => $answer->answer,
	]);
	?></td>
	<td class="actions"><?php
	echo $this->Jquery->ajaxLink(__('Delete'), [
		'url' => ['action' => 'delete_answer', 'answer' => $answer->id],
		'disposition' => 'remove_closest',
		'selector' => 'tr',
	]);
	if ($answer->active) {
		echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $answer->id]]);
	} else {
		echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['controller' => 'Answers', 'action' => 'activate', 'answer' => $answer->id]]);
	}
	?></td>
</tr>
