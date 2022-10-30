<?php
/**
 * @type $game \App\Model\Entity\Game
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
 * @type $spirit \App\Model\Entity\SpiritEntry
 * @type $spirit_obj \App\Module\Spirit
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
