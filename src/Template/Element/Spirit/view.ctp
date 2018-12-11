<?php
$spirit = $this->element("Spirit/view/{$spirit_obj->render_element}",
	compact('team', 'league', 'division', 'spirit', 'spirit_obj'));

if ($spirit) {
	echo $this->Html->tag('fieldset',
		$this->Html->tag('legend', __('Spirit assigned to {0}', $team->name)) . $spirit);
}
