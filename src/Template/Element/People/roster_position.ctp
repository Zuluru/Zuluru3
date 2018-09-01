<?php
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

$teams_table = TableRegistry::get('Teams');
$can_edit_roster = $teams_table->canEditRoster($team, Configure::read('Perm.is_admin'), Configure::read('Perm.is_manager'));
$is_me = ($roster->person_id == Configure::read('Perm.my_id')) || in_array($roster->person_id, $this->UserCache->read('RelativeIDs'));
$permission = ($can_edit_roster === true || (!$division->roster_deadline_passed && $is_me));

if ($permission) {
	echo $this->Jquery->inPlaceWidget(__(Configure::read("sports.{$division->league->sport}.positions.{$roster->position}")), [
		'type' => "{$division->league->sport}_roster_position",
		'url' => [
			'controller' => 'Teams',
			'action' => 'roster_position',
			'team' => $roster->team_id,
			'person' => $roster->person_id,
			'return' => AppController::_return(),
		],
	]);
} else {
	echo __(Configure::read("sports.{$division->league->sport}.positions.{$roster->position}"));
}
