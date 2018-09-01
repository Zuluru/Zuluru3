<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type \App\Model\Entity\Incident $incident
 * @type \App\Model\Entity\Game $game
 * @type \App\Model\Entity\Division $division
 * @type \App\Model\Entity\GameSlot $slot
 * @type \App\Model\Entity\Field $field
 * @type \App\Model\Entity\Team $home_team
 * @type \App\Model\Entity\Team $away_team
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
<br><?= __(Configure::read('UI.field_cap')) ?>: <?= $field->long_name ?></p>
<p><?= $incident->details ?></p>
