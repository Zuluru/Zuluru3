<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */
?>
			<dt class="col-sm-3 text-end"><?= __('Rating Points') ?></dt>
			<dd class="col-sm-9 mb-0">
<?php
if ($game->home_score == $game->away_score && $game->rating_points == 0) {
	echo __('No points were transferred between teams');
}
else {
	if ($game->home_score >= $game->away_score) {
		$winner = $this->element('Teams/block', ['team' => $game->home_team, 'show_shirt' => false]);
		$loser = $this->element('Teams/block', ['team' => $game->away_team, 'show_shirt' => false]);
	}
	else {
		$winner = $this->element('Teams/block', ['team' => $game->away_team, 'show_shirt' => false]);
		$loser = $this->element('Teams/block', ['team' => $game->home_team, 'show_shirt' => false]);
	}
	if ($game->rating_points < 0) {
		$winner_text = __('lose');
		$loser_text = __('gain');
		$transfer = -$game->rating_points;
	} else {
		$winner_text = __('gain');
		$loser_text = __('lose');
		$transfer = $game->rating_points;
	}
	$points = __n('point', 'points', $transfer);
	echo "{$game->rating_points} ($winner $winner_text $transfer $points " .
		__('and') . " $loser $loser_text $transfer $points)";
}
?>

			</dd>
