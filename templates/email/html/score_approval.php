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

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('You have not submitted a score for the game between your team {0} and {1}, starting at {2} on {3} in {4}.',
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	$this->Html->link($opponent->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $opponent->id]], true)),
	$this->Html->link($this->Time->time($game->game_slot->game_start), Router::url(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], true)),
	$this->Time->date($game->game_slot->game_date),
	$division->full_league_name
) ?></p>
<p><?= __('Scores need to be submitted in a timely fashion by both teams to substantiate results and for optimal scheduling of future games.') . ' ' .
	__('Your opponent\'s submission for this game has now been accepted and they have been given a standard spirit score as a result of their timely submission.')
?></p>
<?php
if (Configure::read('scoring.missing_score_spirit_penalty') > 0):
?>
<p><?= __('Your team spirit score has been penalized due to your lack of submission - your opponent\'s Spirit score for your team minus {0} points. Overall team spirit can impact participation in future events.',
	Configure::read('scoring.missing_score_spirit_penalty')
) ?></p>
<p><?= __('If there is an exceptional reason why you were unable to submit your score in time, you may contact your coordinator who will consider reversing the penalty. To avoid such penalties in the future, please be sure to submit your scores promptly.') ?></p>
<?php
endif;
?>
<?= $this->element('Email/html/footer');
