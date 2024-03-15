<?php
/**
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\SpiritEntry $spirit
 * @var \App\Module\Spirit $spirit_obj
 */

if ($spirit) {
	$identity = $this->Authorize->getIdentity();
	echo $this->element('FormBuilder/view', [
		'questions' => $spirit_obj->questions,
		'answers' => $spirit,
		'show_restricted' => $identity->isManagerOf($league) || $identity->isCoordinatorOf($division),
	]);
	if ($division->most_spirited != 'never' && !empty($spirit->most_spirited)) {
		echo $this->Html->para(null, __('Most spirited player') . ': ' .
				$this->element('People/block', ['person' => $spirit->most_spirited]));
	}
}
