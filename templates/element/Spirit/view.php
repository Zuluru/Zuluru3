<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\SpiritEntry $spirit
 * @var \App\Module\Spirit $spirit_obj
 */

use Cake\Core\Configure;

$output = $this->element("Spirit/view/{$spirit_obj->render_element}",
	compact('team', 'league', 'division', 'spirit', 'spirit_obj'));

if ($output) {
	if (Configure::read('scoring.spirit_entry_by')) {
		if ($spirit->created_team_id) {
			$creator = ' ' . __('by opponent');
		} else {
			$creator = ' ' . __('by official');
		}
	} else {
		$creator = '';
	}

	echo $this->Html->tag('fieldset',
		$this->Html->tag('legend', __('Spirit assigned to {0}{1}', $team->name, $creator)) . $output);
}
