<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $roster \App\Model\Entity\TeamsPerson
 * @type $team \App\Model\Entity\Team
 * @type $division \App\Model\Entity\Division
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
			'team' => $roster->team_id,
			'person' => $roster->person_id,
			'return' => AppController::_return(),
		],
	]);
} else {
	echo __(Configure::read("sports.{$division->league->sport}.positions.{$roster->position}"));
}
