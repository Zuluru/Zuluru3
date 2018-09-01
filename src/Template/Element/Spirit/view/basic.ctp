<?php
use Cake\Core\Configure;

if ($spirit) {
	if (Configure::read('Perm.is_admin') || $is_coordinator || $league->display_sotg === 'all') {
		echo $this->element('FormBuilder/view', ['questions' => $spirit_obj->questions, 'answers' => $spirit]);
	}
	if ($division->most_spirited != 'never' && !empty($spirit->most_spirited)) {
		echo $this->Html->para(null, __('Most spirited player') . ': ' .
				$this->element('People/block', ['person' => $spirit->most_spirited]));
	}
}
