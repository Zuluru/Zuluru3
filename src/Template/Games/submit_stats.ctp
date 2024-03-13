<?php
/**
 * @var \App\Model\Entity\Game $game
 * @var int $team_id
 * @var \App\Module\Sport $sport_obj
 */

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Submit Game Stats'));
?>

<?php
if ($team_id == $game->home_team->id || $team_id === null) {
	$this_team = $game->home_team;
	$opponent = $game->away_team;
} else {
	$this_team = $game->away_team;
	$opponent = $game->home_team;
}
?>

<div class="games form">
	<h2><?= __('Submit Game Stats') ?></h2>

	<p><?php
	echo __('Submit the stats for the {0} game at {1} between {2} and {3}.',
		$this->Time->dateTimeRange($game->game_slot),
		$this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']),
		$this->element('Teams/block', ['team' => $this_team, 'show_shirt' => false]),
		$this->element('Teams/block', ['team' => $opponent, 'show_shirt' => false])
	);
	?></p>
	<p><?php
	if ($game->isFinalized()) {
		$msg = __('The score for this game has been confirmed as {0} {1}, {2} {3}.');
		if ($team_id === null || $team_id == $game->home_team->id) {
			$this_team->score = $game->home_score;
			$opponent->score = $game->away_score;
		} else {
			$this_team->score = $game->away_score;
			$opponent->score = $game->home_score;
		}
	} else if ($team_id !== null) {
		$msg = __('You have submitted the score for this game as {0} {1}, {2} {3}, but this has not been confirmed by your opponent.');
		$this_team->score = $game->score_entries[$team_id]->score_for;
		$opponent->score = $game->score_entries[$team_id]->score_against;
	} else {
		$msg = __('A score of {0} {1}, {2} {3} has been submitted for this game, but this has not been confirmed.');
		$entry = current($game->score_entries);
		if ($entry->team_id == $this_team->id) {
			$this_team->score = $entry->score_for;
			$opponent->score = $entry->score_against;
		} else {
			$this_team->score = $entry->score_against;
			$opponent->score = $entry->score_for;
		}
	}
	echo __($msg, $this_team->name, $this_team->score, $opponent->name, $opponent->score);
	?>
	</p>

	<?= $this->element("Games/stats_entry/{$game->division->league->sport}") ?>

	<p>
	<?= $this->Jquery->toggleLinkPair(
		__('Show Only Applicable Stat Options'),
		'unapplicable',
		__('Show All Stat Options'),
		'applicable'
	) ?>

/

	<?= $this->Jquery->toggleLinkPair(
		__('Show All Players'),
		'show_attending',
		__('Show Only Attending Players'),
		'attendance_details'
	) ?>
	</p>

<?php
echo $this->Form->create($game, ['align' => 'horizontal']);

if (isset($attendance)) {
	echo $this->element('Games/stats_entry', ['stat_types' => $game->division->league->stat_types, 'attendance' => $attendance]);
} else {
	echo $this->Html->tag('h3', $this->element('Teams/block', ['team' => $game->home_team, 'show_shirt' => false]));
	echo $this->element('Games/stats_entry', ['stat_types' => $game->division->league->stat_types, 'attendance' => $home_attendance]);
	echo $this->Html->tag('h3', $this->element('Teams/block', ['team' => $game->away_team, 'show_shirt' => false]));
	echo $this->element('Games/stats_entry', ['stat_types' => $game->division->league->stat_types, 'attendance' => $away_attendance]);
}
?>

	<p>
	<?= $this->Jquery->toggleLinkPair(
		__('Show Only Applicable Stat Options'),
		'unapplicable',
		__('Show All Stat Options'),
		'applicable'
	) ?>

/

	<?= $this->Jquery->toggleLinkPair(
		__('Show All Players'),
		'show_attending',
		__('Show Only Attending Players'),
		'attendance_details'
	) ?>
	</p>

	<div class="submit">
<?php
if (isset($attendance)) {
	echo $this->Form->button(__('Submit'), ['class' => 'btn-success', 'onClick' => "return check_score({$this_team->score}, {$opponent->score}, {$this_team->id});"]);
} else {
	echo $this->Form->button(__('Submit'), ['class' => 'btn-success', 'onClick' => "return check_score({$this_team->score}, {$opponent->score}, {$this_team->id}) && check_score({$opponent->score}, {$this_team->score}, {$opponent->id});"]);
}
?>

		<?= $this->Form->button(__('Reset'), ['type' => 'reset']) ?>

		<?= $this->Form->end() ?>
	</div>
</div>

<?php
$this->Html->script(["sport_{$game->division->league->sport}.js"], ['block' => true]);
$stat_js = [];
foreach ($game->division->league->stat_types as $stat_type) {
	if (!empty($stat_type->validation)) {
		$func = "validate_{$stat_type->validation}";
		if (method_exists($sport_obj, $func)) {
			$stat_js = array_merge($stat_js, $sport_obj->$func($stat_type));
		} else {
			trigger_error("Validation handler {$stat_type->validation} was not found in the {$game->division->league->sport} module!", E_USER_ERROR);
		}
	}
}
$correct = __('Please correct this and re-submit.');
$confirm = __('Click OK to proceed, or Cancel to enter more stats.');
echo $this->Html->scriptBlock("
function check_score(team_score, opponent_score, team_id) {
	var alert_msg = '';
	var confirm_msg = '';
	" . implode("\n	", $stat_js) . "
	if (alert_msg != '') {
		alert(alert_msg + '\\n\\n$correct');
		return false;
	}
	if (confirm_msg != '') {
		return confirm(confirm_msg + '\\n\\n$confirm');
	}
	return true;
}
");

$this->Html->scriptBlock("
zjQuery('input').on('change', function () { statsInputChanged(zjQuery(this)); });
zjQuery('tr#sub_row').find('input[class^=stat_]').each(function() { statsInputChanged(zjQuery(this)); });
zjQuery('.unapplicable, .attendance_details').hide();
", ['buffer' => true]);

echo $this->element('Games/attendance_div');
