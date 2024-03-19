<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Module\Spirit $spirit_obj
 */

$spirit = $this->element("Spirit/view/{$spirit_obj->render_element}",
	compact('team', 'league', 'division', 'spirit', 'spirit_obj'));

if ($spirit) {
	echo $this->Html->tag('fieldset',
		$this->Html->tag('legend', __('Spirit assigned to {0}', $team->name)) . $spirit);
}
