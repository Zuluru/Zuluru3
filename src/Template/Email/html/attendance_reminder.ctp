<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $team \App\Model\Entity\Team
 * @type $opponent \App\Model\Entity\Team
 * @type $game \App\Model\Entity\Game
 * @type $person \App\Model\Entity\Person
 * @type $status int
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

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?php
if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED) {
	echo __('You have not yet indicated your attendance for the {0} game{1} at {2} from {3} to {4} on {5}.',
		$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
		$opponent_text,
		$this->Html->link($game->game_slot->field->long_name, Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true)),
		$this->Html->link($this->Time->time($game->game_slot->game_start), Router::url(['controller' => 'Games', 'action' => 'view', 'game' => $game->id], true)),
		$this->Time->time($game->game_slot->display_game_end),
		$this->Time->date($game->game_slot->game_date)
	);
} else {
	echo __('You are currently listed as {0} for the {1} game{2} at {3} from {4} to {5} on {6}.',
		Configure::read("attendance.$status"),
		$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
		$opponent_text,
		$this->Html->link($game->game_slot->field->long_name, Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true)),
		$this->Html->link($this->Time->time($game->game_slot->game_start), Router::url(['controller' => 'Games', 'action' => 'view', 'game' => $game->id], true)),
		$this->Time->time($game->game_slot->display_game_end),
		$this->Time->date($game->game_slot->game_date)
	);
}
?></p>
<?php
if ($status == ATTENDANCE_INVITED):
?>
<p><?= __('The coach or captain has invited you to play in this game. However, when teams are short, coaches and captains will often invite a number of people to fill in, so it\'s possible that even if you confirm now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your coach or captain that you are needed before the game.') ?></p>
<?php
endif;

if ($status == ATTENDANCE_INVITED || in_array($person->_joinData->role, Configure::read('regular_roster_roles'))):
?>
<p><?= __('If you are able to play, {0}.',
	$this->Html->link(__('click here'),
		Router::url(['controller' => 'Games', 'action' => 'attendance_change', 'team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING], true))
) ?></p>
<?php
elseif ($status != ATTENDANCE_ATTENDING && !in_array($person->_joinData->role, Configure::read('regular_roster_roles'))):
?>
<p><?= __('If you are available to play, {0}.',
	$this->Html->link(__('click here'),
		Router::url(['controller' => 'Games', 'action' => 'attendance_change', 'team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_AVAILABLE], true))
) ?></p>
<?php
endif;
?>
<p><?= __('If you are unavailable to play, {0}.',
	$this->Html->link(__('click here'),
		Router::url(['controller' => 'Games', 'action' => 'attendance_change', 'team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT], true))
) ?></p>
<p><?= __('Note that you can {0}, giving your coach or captain advance notice of vacations or other planned absences.',
		$this->Html->link(__('set your attendance in advance'),
			Router::url(['controller' => 'Teams', 'action' => 'attendance', 'team' => $team->id], true)
		)
	) . ' ' .
	__('You need to be logged into the website to update this.')
?></p>
<?= $this->element('Email/html/footer');
