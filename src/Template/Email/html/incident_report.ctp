<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $incident \App\Model\Entity\Incident
 * @type $game \App\Model\Entity\Game
 * @type $division \App\Model\Entity\Division
 * @type $slot \App\Model\Entity\GameSlot
 * @type $field \App\Model\Entity\Field
 * @type $home_team \App\Model\Entity\Team
 * @type $away_team \App\Model\Entity\Team
 */
?>

<p><?= __('The following incident report was submitted:') ?></p>
<p><?= __('League') ?>: <?= $this->Html->link($division->league->name,
	Router::url(['controller' => 'Leagues', 'action' => 'view', 'league' => $division->league_id], true)) ?>
<br><?= __('Game') ?>: <?= $this->Html->link($game->id,
	Router::url(['controller' => 'Games', 'action' => 'view', 'game' => $game->id], true)) ?>
<br><?= __('Date') ?>: <?= $this->Time->fulldate($slot->game_date) ?>
<br><?= __('Time') ?>: <?= $this->Time->time($slot->game_start) ?>
<br><?= __('Home Team') ?>: <?php
echo $this->Html->link($home_team->name,
	Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $home_team->id], true));
if ($home_team->id == $incident->team_id) {
	echo ' ' . __('(submitter)');
}
?>
<?php
if (!empty($away_team)):
?>
<br><?= __('Away Team') ?>: <?php
	echo $this->Html->link($away_team->name,
		Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $away_team->id], true));
	if ($away_team->id == $incident->team_id) {
		echo ' ' . __('(submitter)');
	}
?>
<?php
endif;
?>
<br><?= Configure::read('UI.field_cap') ?>: <?= $field->long_name ?></p>
<p><?= $incident->details ?></p>
