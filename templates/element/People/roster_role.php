<?php
/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\TeamsPerson $roster
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Division $division
 */

use App\Authorization\ContextResource;
use App\Controller\AppController;
use Cake\Core\Configure;
?>
<span><?php
$permission = $this->Authorize->can('roster_role', new ContextResource($team, ['division' => $division, 'roster' => $roster]));
$identity = $this->Authorize->getIdentity();
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
			if ($permission && $identity->isCaptainOf($team)) {
				// Captains can only remove invitations that they sent
				$remove = true;
			}
			$type = __('invitation');
			break;

		case ROSTER_REQUESTED:
			echo __('requested');
			if ($permission && $identity->isMe($roster)) {
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
				'?' => [
					'team' => $roster->team_id,
					'person' => $roster->person_id,
				],
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
				'?' => [
					'team' => $roster->team_id,
					'person' => $roster->person_id,
				],
			],
			'confirm' => __('Are you sure you want to accept this {0}?', $type),
			'disposition' => 'replace_closest',
			'selector' => 'span',
		]) . ' ' . __('or') . ' ' . $this->Jquery->ajaxLink(__('decline'), [
			'url' => [
				'controller' => 'Teams',
				'action' => 'roster_decline',
				'?' => [
					'team' => $roster->team_id,
					'person' => $roster->person_id,
				],
			],
			'confirm' => __('Are you sure you want to decline this {0}?', $type),
			'disposition' => 'remove_closest',
			'selector' => 'tr',
		]);
	}

	echo ']';
}
?></span><?php // This is here to ensure there's no whitespace after the span close tag
