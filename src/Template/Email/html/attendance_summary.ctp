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
	$opponent_text = __(' against {0}', $this->Html->link($opponent->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $opponent->id], true)) . $shirt_text);
} else {
	$opponent_text = '';
}
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('This is your attendance summary for the {0} game{1} at {2} from {3} to {4} on {5}.',
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	$opponent_text,
	$this->Html->link($game->game_slot->field->long_name, Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true)),
	$this->Html->link($this->Time->time($game->game_slot->game_start), Router::url(['controller' => 'Games', 'action' => 'view', 'game' => $game->id], true)),
	$this->Time->time($game->game_slot->display_game_end),
	$this->Time->date($game->game_slot->game_date)
) ?></p>
<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty($players)) {
			$text .= '<br />' . count($players) . ' ' . __x('gender', $gender) . ': ' . implode(', ', $players);
		}
	}
	if (!empty($text)) {
		echo $this->Html->para(null, Configure::read("attendance.$status") . $text);
	}
}
?>
<p><?= __('You can {0}.',
		$this->Html->link(__('update this or check up-to-the-minute details'),
			Router::url(['controller' => 'Games', 'action' => 'attendance', 'team' => $team->id, 'game' => $game->id], true)
		)
	) . ' ' .
	__('You need to be logged into the website to update this.')
?></p>
<?= $this->element('Email/html/footer');
