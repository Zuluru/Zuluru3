<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Person $submitter
 */

use App\Model\Entity\Game;
use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('{0} vs {1}', $team->name, $opponent->name));
$this->Breadcrumbs->add(__('Live Game Scoring'));
?>

<div class="games form">
<h2><?= __('Live Game Scoring') ?></h2>

<?php
echo $this->Html->para(null, __('Submit {0} for the {1} game at {2} between {3} and {4}.',
	__('live results'),
	$this->Time->dateTimeRange($game->game_slot),
	$this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']),
	$this->element('Teams/block', ['team' => $team, 'show_shirt' => false]),
	$this->element('Teams/block', ['team' => $opponent, 'show_shirt' => false])
));

if (!empty($game->score_entries)) {
	$entry = current($game->score_entries);
	if ($entry->team_id === null || $entry->team_id == $team->id) {
		$team_score = $entry->score_for;
		$opponent_score = $entry->score_against;
	} else {
		$team_score = $entry->score_against;
		$opponent_score = $entry->score_for;
	}
} else {
	$team_score = $opponent_score = 0;
}
$has_stats = $game->division->league->hasStats();

$timeouts = collection($game->score_details)->match(['team_id' => $team->id, 'play' => 'Timeout'])->toArray();
echo $this->element('Games/score_box', ['game' => $game, 'submitter' => $submitter, 'team' => $team, 'score' => $team_score, 'has_stats' => $has_stats, 'timeouts' => count($timeouts)]);

$timeouts = collection($game->score_details)->match(['team_id' => $opponent->id, 'play' => 'Timeout'])->toArray();
echo $this->element('Games/score_box', ['game' => $game, 'submitter' => $submitter, 'team' => $opponent, 'score' => $opponent_score, 'has_stats' => $has_stats, 'timeouts' => count($timeouts)]);
?>
<div class="actions columns clear-float">
<?php
if (!$submitter) {
	$url = ['action' => 'edit', '?' => ['game' => $game->id, 'stats' => $has_stats]];
} else {
	$url = ['action' => 'submit', '?' => ['game' => $game->id, 'team' => $submitter]];
}
echo $this->Bootstrap->navPills([
	$this->Html->link(__('Finalize'), $url, ['class' => $this->Bootstrap->navPillLinkClasses()]),
]);
?>
</div>
</div>

<?php
if (empty($game->score_details)):
?>
<div id="StartDetails<?= $team->id ?>" title="<?= __('Game Start Details') ?>" class="form">
<div class="zuluru">
<?php
	$url = ['controller' => 'Games', 'action' => 'play', 'game' => $game->id, 'team' => $submitter];
	echo $this->Form->create(null, [
		'id' => "StartForm{$team->id}",
		'url' => $url,
	]);

	$start_text = Configure::read("sports.{$game->division->league->sport}.start.live_score");
	if ($start_text) {
		echo $this->Form->control('team_id', [
				'label' => __($start_text),
				'options' => [
					$team->id => $team->name,
					$opponent->id => $opponent->name,
				],
		]);
	} else {
		echo $this->Form->hidden('team_id', [
				'value' => $team->id,
		]);
	}

	echo $this->Form->hidden('play', ['value' => 'Start']);
	echo $this->Form->end();
?>
<p class="warning-message"><?= __('Do not click "Submit" until the game actually starts, as this initiates an internal timer used to track the times of plays.') ?></p>
</div>
</div>
<?php
	$submit = __('Submit');
	$this->Html->scriptBlock("
		zjQuery('#StartDetails{$team->id}').dialog({
			autoOpen: true,
			buttons: {
				'$submit': function () {
					zjQuery(this).dialog('close');
					zjQuery('#StartForm{$team->id}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function (message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					zjQuery('#StartForm{$team->id}').each(function(){
						this.reset();
					});
				}
			},
			modal: true,
			resizable: false,
			width: 500
		});
	", ['buffer' => true]);

endif;

$this->Html->script(['jquery.form.js'], ['block' => true]);
