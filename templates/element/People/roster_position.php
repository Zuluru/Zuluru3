<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\TeamsPerson $roster
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Division $division
 */

use App\Authorization\ContextResource;
use App\Controller\AppController;
use Cake\Core\Configure;

if ($this->Authorize->can('roster_position', new ContextResource($team, ['division' => $division, 'roster' => $roster]))) {
	echo $this->Jquery->inPlaceWidget(__(Configure::read("sports.{$division->league->sport}.positions.{$roster->position}")), [
		'type' => "{$division->league->sport}_roster_position",
		'url' => [
			'controller' => 'Teams',
			'action' => 'roster_position',
			'?' => [
				'team' => $roster->team_id,
				'person' => $roster->person_id,
				'return' => AppController::_return(),
			],
		],
	]);
} else {
	echo __(Configure::read("sports.{$division->league->sport}.positions.{$roster->position}"));
}
