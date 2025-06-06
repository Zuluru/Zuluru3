<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $captains) ?>


<?= __('You have not submitted a score for the game between your team {0} and {1}, starting at {2} on {3} in {4}.',
	$team->name,
	$opponent ? $opponent->name : __('your opponent'),
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->game_slot->game_date),
	$division->full_league_name
) ?>


<?= __('Scores need to be submitted in a timely fashion by both teams to substantiate results and for optimal scheduling of future games.') . ' ' .
	__('We ask you to please submit the score as soon as possible.') . ' ' .
	__('You can submit the score for this game at')
?>

<?= Router::url(['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $team->id]], true) ?>


<?php
if ($division->finalize_after > 0):
	if ($division->finalize_after > 48) {
		$count = intval($division->finalize_after / 24);
		$finalize_text = __n('{0} day', '{0} days', $count, $count);
	} else {
		$finalize_text = __n('{0} hour', '{0} hours', $division->finalize_after, $division->finalize_after);
	}

	if (Configure::read('scoring.missing_score_spirit_penalty') > 0) {
		$penalty_text = __(' and a loss of {0} Spirit points', Configure::read('scoring.missing_score_spirit_penalty'));
	} else {
		$penalty_text = '';
	}
?>
<?= __('Remember to report the score within {0} of your game to avoid automatic score approval{1}.',
	$finalize_text,
	$penalty_text
) ?>


<?php
endif;
?>
<?= $this->element('email/text/footer');
