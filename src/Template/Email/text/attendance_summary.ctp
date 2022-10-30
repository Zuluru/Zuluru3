<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $team \App\Model\Entity\Team
 * @type $opponent \App\Model\Entity\Team
 * @type $game \App\Model\Entity\Game
 * @type $summary string[][][]
 */

if ($opponent) {
	if (Configure::read('feature.shirt_colour') && !empty($opponent->shirt_colour)) {
		$shirt_text = __(' (they wear {0})', $opponent->shirt_colour);
	} else {
		$shirt_text = '';
	}
	$opponent_text = __(' against {0}', $opponent->name . $shirt_text);
} else {
	$opponent_text = '';
}
?>

<?= __('Dear {0},', $captains) ?>


<?= __('This is your attendance summary for the {0} game{1} at {2} from {3} to {4} on {5}.',
	$team->name,
	$opponent_text,
	$game->game_slot->field->long_name . __(' ({0})', Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true)),
	$this->Time->time($game->game_slot->game_start),
	$this->Time->time($game->game_slot->display_game_end),
	$this->Time->date($game->game_slot->game_date)
) ?>


<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty($players)) {
			$text .= "\n" . count($players) . ' ' . __x('gender', $gender) . ': ' . implode(', ', $players);
		}
	}
	if (!empty($text)) {
		echo Configure::read("attendance.$status") . $text . "\n\n";
	}
}
?>
<?= __('You can update this or check up-to-the-minute details here:') ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance', 'team' => $team->id, 'game' => $game->id], true) ?>


<?= __('You need to be logged into the website to update this.') ?>


<?= $this->element('Email/text/footer');
