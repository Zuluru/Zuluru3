<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */

use Cake\Core\Configure;

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(null, ['align' => 'horizontal']);

$team_names = [
	$game->home_team->id => $game->home_team->name,
	$game->away_team->id => $game->away_team->name
];

$roster = [];
if (!empty($game->division->league->stat_types)) {
	// Build the roster options
	foreach (['home_team', 'away_team'] as $key) {
		$roster[$game->$key->id] = collection($game->$key->people)->combine('id', function ($person) {
			$option = $person->full_name;
			if (Configure::read('feature.shirt_numbers') && $person->_joinData->number !== null && $person->_joinData->number !== '') {
				$option = "{$person->_joinData->number} $option";
				if ($person->_joinData->number < 10) {
					$option = " $option";
				}
			}
			return $option;
		})->toArray();
		asort($roster[$game->$key->id]);
	}
}

echo $this->element('Games/edit_boxscore_line', [
	'detail' => $detail,
	'year' => $this->getRequest()->getData('add_detail.created.year'),
	'month' => $this->getRequest()->getData('add_detail.created.month'),
	'day' => $this->getRequest()->getData('add_detail.created.day'),
	'team_names' => $team_names,
	'roster' => $roster,
]);
