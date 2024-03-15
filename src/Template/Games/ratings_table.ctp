<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Game') . ' ' . $game->id);
$this->Breadcrumbs->add(__('Ratings Table'));
?>

<div class="games view">
<h2><?= __('View Ratings Table') ?></h2>
<p><?= __('The number of rating points transferred depends on several factors:') ?></p>
<ul>
<li><?= __('the total score') ?></li>
<li><?= __('the difference in score') ?></li>
<li><?= __('and the current rating of both teams') ?></li>
</ul>

<p><?= __('How to read the table below:') ?></p>
<ul>
<li><?= __('Find the \'home\' team\'s score along the left.') ?></li>
<li><?= __('Find the \'away\' team\'s score along the top.') ?></li>
<li><?= __('The points shown in the table where these two scores intersect are the number of rating points that will be transfered from the losing team to the winning team.') ?></li>
</ul>

<p><?= __('A tie does not necessarily mean 0 rating points will be transfered. Unless the two team\'s rating scores are very close, one team is expected to win. If that team doesn\'t win, they will lose rating points. The opposite is also true: if a team is expected to lose, but they tie, they will gain some rating points.') ?></p>

<p><?= __('Ties are shown from the home team\'s perspective. So, a negative value indicates that in the event of a tie, the home team will lose rating points (and the away team will gain them).') ?></p>
<?php
if (!isset($rating_home)) {
	$rating_home = $game->home_team->rating;
	$rating_away = $game->away_team->rating;
	$type = __('current');
} else {
	$type = __('"what if"');
}
$expected_home = $ratings_obj->calculateExpectedWin($rating_home, $rating_away);
$expected_away = $ratings_obj->calculateExpectedWin($rating_away, $rating_home);
?>

<p><?= __('Home') ?>: <strong><?= $game->home_team->name ?></strong>, <?= $type ?> <?= __('rating of') ?> <strong><?= $rating_home ?></strong>, <?= sprintf('(%0.1f%%)', $expected_home * 100) ?> <?= __('chance to win') ?>
<br><?= __('Away') ?>: <strong><?= $game->away_team->name ?></strong>, <?= $type ?> <?= __('rating of') ?> <strong><?= $rating_away ?></strong>, <?= sprintf('(%0.1f%%)', $expected_away * 100) ?> <?= __('chance to win') ?>

<?php
$header = ['&nbsp;'];
$rows = [];
for ($h = 0; $h <= $max_score; $h++) {
	$header[] = $h;
	$row = [$h];
	for ($a = 0; $a <= $max_score; $a++) {
		if ($h > $a) {
			// home win
			$change = $ratings_obj->calculateRatingsChange($h, $a, $expected_home);
			$row[] = [$change, ['title' => __('{0} wins {1} to {2}, takes {3} rating points from {4}', $game->home_team->name, $h, $a, $change, $game->away_team->name), 'class' => 'highlight-message']];
		} else if ($h == $a) {
			// treat as a home win
			$change = $ratings_obj->calculateRatingsChange($h, $a, $expected_home);
			$row[] = [$change, ['title' => __('Tie {0} to {0}, {1} takes {2} rating points from {3}', $h, $game->home_team->name, $change, $game->away_team->name), 'class' => 'highlight-message']];
		} else {
			$change = $ratings_obj->calculateRatingsChange($h, $a, $expected_away);
			$row[] = [$change, ['title' => __('{0} wins {1} to {2}, takes {3} rating points from {4}', $game->away_team->name, $a, $h, $change, $game->home_team->name)]];
		}
	}
	$rows[] = $row ;
}
?>

	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<?= $this->Html->tableHeaders($header) ?>
		</thead>
		<tbody>
			<?= $this->Html->tableCells($rows) ?>
		</tbody>
	</table>
	</div>

	<div class="form">
		<p><?= __('What if the teams had different ratings? Check it here:') ?></p>
<?= $this->Form->create($game, ['align' => 'horizontal']) ?>
<?php
echo $this->Form->control('rating_home', [
		'label' => $game->home_team->name,
		'size' => 5,
]);
echo $this->Form->control('rating_away', [
		'label' => $game->away_team->name,
		'size' => 5,
]);
echo $this->Form->button(__('What if?'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
	</div>
</div>
