<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Incident $incident
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\GameSlot $slot
 * @var \App\Model\Entity\Field $field
 * @var \App\Model\Entity\Team $home_team
 * @var \App\Model\Entity\Team $away_team
 */

use Cake\Core\Configure;
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
