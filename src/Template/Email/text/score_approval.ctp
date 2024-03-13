<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $captains) ?>


<?= __('You have not submitted a score for the game between your team {0} and {1}, starting at {2} on {3} in {4}.',
	$team->name,
	$opponent->name,
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->game_slot->game_date),
	$division->full_league_name
) ?>


<?= __('Scores need to be submitted in a timely fashion by both teams to substantiate results and for optimal scheduling of future games.') . ' ' .
	__('Your opponent\'s submission for this game has now been accepted and they have been given a standard spirit score as a result of their timely submission.')
?>


<?php
if (Configure::read('scoring.missing_score_spirit_penalty') > 0):
?>
<?= __('Your team spirit score has been penalized due to your lack of submission - your opponent\'s Spirit score for your team minus {0} points. Overall team spirit can impact participation in future events.',
	Configure::read('scoring.missing_score_spirit_penalty')
) ?>


<?= __('If there is an exceptional reason why you were unable to submit your score in time, you may contact your coordinator who will consider reversing the penalty. To avoid such penalties in the future, please be sure to submit your scores promptly.') ?>


<?php
endif;
?>
<?= $this->element('Email/text/footer');
