<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $roster \App\Model\Entity\TeamsPerson
 * @type $team \App\Model\Entity\Team
 * @type $division \App\Model\Entity\Division
 */

use App\Authorization\ContextResource;

if ($this->Authorize->can('numbers', new ContextResource($team, ['division' => $division, 'roster' => $roster]))) {
	$data = [
		'url' => ['action' => 'numbers', 'team' => $team->id, 'person' => $person->id],
		'dialog' => 'number_entry_div',
	];
	if (!empty($roster->number) || $roster->number === '0') {
		$link_text = $roster->number;
		$data['value'] = $roster->number;
	} else {
		$link_text = $this->Html->iconImg('add_24.png',
			['alt' => __('Add Number'), 'title' => __('Add Number')]);
	}
	echo $this->Jquery->ajaxLink($link_text, $data, ['escape' => false]);
} else {
	echo $roster->number;
}
