<?php
use Cake\Core\Configure;

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

<?= __('The following incident report was submitted:') ?>


<?= __('League') ?>: <?= $division->league->name ?>

<?= __('Game') ?>: <?= $game->id ?>

<?= __('Date') ?>: <?= $this->Time->fulldate($slot->game_date) ?>

<?= __('Time') ?>: <?= $this->Time->time($slot->game_start) ?>

<?= __('Home Team') ?>: <?php
echo $home_team->name;
if ($home_team->id == $incident->team_id) {
	echo ' ' . __('(submitter)');
}
?>

<?php
if (!empty($away_team)):
?>
<?= __('Away Team') ?>: <?php
	echo $away_team->name;
	if ($away_team->id == $incident->team_id) {
		echo ' ' . __('(submitter)');
	}
?>

<?php
endif;
?>
<?= Configure::read('UI.field_cap') ?>: <?= $field->long_name ?>


<?= $incident->details ?>
