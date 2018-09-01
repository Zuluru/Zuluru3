<?php
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
?>
<span><?php
$teams_table = TableRegistry::get('Teams');
$can_edit_roster = $teams_table->canEditRoster($team, Configure::read('Perm.is_admin'), Configure::read('Perm.is_manager'));
$is_me = ($roster->person_id == Configure::read('Perm.my_id')) || in_array($roster->person_id, $this->UserCache->read('RelativeIDs'));
$permission = ($can_edit_roster === true || (!$division->roster_deadline_passed && $is_me));

$approved = ($roster->status == ROSTER_APPROVED);

if ($permission && $approved) {
	echo $this->Jquery->inPlaceWidget(__(Configure::read("options.roster_role.{$roster->role}")), [
		'type' => 'roster_role',
		'url' => [
			'controller' => 'Teams',
			'action' => 'roster_role',
			'team' => $roster->team_id,
			'person' => $roster->person_id,
			'return' => AppController::_return(),
		],
	]);
} else {
	echo __(Configure::read("options.roster_role.{$roster->role}"));
}

if (!$approved) {
	echo ' [';
	switch ($roster->status) {
		case ROSTER_INVITED:
			echo __('invited');
			if ($permission && isset($is_captain) && $is_captain) {
				// Captains can only remove invitations that they sent
				$remove = true;
			}
			$type = __('invitation');
			break;

		case ROSTER_REQUESTED:
			echo __('requested');
			if ($permission && $roster->person_id == Configure::read('Perm.my_id')) {
				// Players can only remove requests that they sent
				$remove = true;
			}
			$type = __('request');
			break;
	}

	if (isset($remove)) {
		echo ': ' . $this->Jquery->ajaxLink(__('remove'), [
			'url' => [
				'controller' => 'Teams',
				'action' => 'roster_decline',
				'team' => $roster->team_id,
				'person' => $roster->person_id,
			],
			'confirm' => __('Are you sure you want to remove this {0}?', $type),
			'disposition' => 'remove_closest',
			'selector' => 'tr',
		]);
	} else if ($permission) {
		echo ': ' . $this->Jquery->ajaxLink(__('accept'), [
			'url' => [
				'controller' => 'Teams',
				'action' => 'roster_accept',
				'team' => $roster->team_id,
				'person' => $roster->person_id,
			],
			'confirm' => __('Are you sure you want to accept this {0}?', $type),
			'disposition' => 'replace_closest',
			'selector' => 'span',
		]) . ' ' . __('or') . ' ' . $this->Jquery->ajaxLink(__('decline'), [
			'url' => [
				'controller' => 'Teams',
				'action' => 'roster_decline',
				'team' => $roster->team_id,
				'person' => $roster->person_id,
			],
			'confirm' => __('Are you sure you want to decline this {0}?', $type),
			'disposition' => 'remove_closest',
			'selector' => 'tr',
		]);
	}

	echo ']';
}
?></span><?php // This is here to ensure there's no whitespace after the span close tag
