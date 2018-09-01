<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

$teams_table = TableRegistry::get('Teams');
$can_edit_roster = $teams_table->canEditRoster($team, Configure::read('Perm.is_admin'), Configure::read('Perm.is_manager'));
$is_me = ($roster->person_id == Configure::read('Perm.my_id')) || in_array($roster->person_id, $this->UserCache->read('RelativeIDs'));
$permission = ($can_edit_roster === true || (!$division->roster_deadline_passed && $is_me));

if ($permission) {
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
