<?php
use Cake\Core\Configure;

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
