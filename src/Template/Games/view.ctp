<?php
/**
 * @type $game \App\Model\Entity\Game
 * @type $ratings_obj \App\Module\Ratings
 * @type $spirit_obj \App\Module\Spirit
 * @type $league_obj \App\Module\LeagueType
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use App\Controller\AppController;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Game') . ' ' . $game->id);
$this->Html->addCrumb(__('View'));

$preliminary = ($game->home_team_id === null || ($game->division->schedule_type != 'competition' && $game->away_team_id === null));

$division_context = new ContextResource($game->division, ['league' => $game->division->league]);
$game_context = new ContextResource($game, ['league' => $game->division->league, 'division' => $game->division, 'ratings_obj' => $ratings_obj, 'home_team' => $game->home_team, 'away_team' => $game->away_team]);
$show_spirit = $this->Authorize->can('view_spirit', $division_context);
$show_spirit_scores = $show_spirit && $this->Authorize->can('view_spirit_scores', $division_context);

$carbon_flip_options = [
	2 => __('{0} won', $game->home_team_id !== null ? $game->home_team->name : $game->home_dependency),
	0 => __('{0} won', $game->away_team_id !== null ? $game->away_team->name : $game->away_dependency),
	1 => __('tie'),
];
?>

<div class="games view">
	<h2><?= __('View Game') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('League') . '/' . __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt><?= $game->division->schedule_type == 'competition' ? __('Team') : __('Home Team') ?></dt>
		<dd><?php
			if ($game->home_team_id === null) {
				if ($game->has('home_dependency')) {
					echo $game->home_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->home_team]);
				if ($game->has('home_dependency')) {
					echo " ({$game->home_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo __(' ({0})', __('currently rated: {0}', $game->home_team->rating));
					if (!$preliminary && !$game->isFinalized() && $game->division->schedule_type != 'competition') {
						printf(' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game->home_team->rating, $game->away_team->rating) * 100, __('chance to win'));
					}
				}
			}
		?></dd>
<?php
if ($game->division->schedule_type != 'competition'):
?>
		<dt><?= __('Away Team') ?></dt>
		<dd><?php
			if ($game->away_team_id === null) {
				if ($game->has('away_dependency')) {
					echo $game->away_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->away_team]);
				if ($game->has('away_dependency')) {
					echo " ({$game->away_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo __(' ({0})', __('currently rated: {0}', $game->away_team->rating));
					if (!$preliminary && !$game->isFinalized()) {
						printf(' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game->away_team->rating, $game->home_team->rating) * 100, __('chance to win'));
					}
				}
			}
		?></dd>
<?php
endif;

if ($game->home_dependency_type != 'copy'):
?>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
<?php
endif;
?>
		<dt><?= __('Game Status') ?></dt>
		<dd><?= __(Inflector::humanize ($game->status)) ?></dd>
<?php
if ($game->division->schedule_type == 'roundrobin' && $game->round):
?>
		<dt><?= __('Round') ?></dt>
		<dd><?= $game->round ?></dd>
<?php
endif;

if ($this->Authorize->can('email_captains', $game)):
	if ($game->home_team) {
		$captains = $game->home_team->people;
	} else {
		$captains = [];
	}
	if ($game->away_team) {
		$captains = array_merge($captains, $game->away_team->people);
	}
	if (!empty($captains)):
?>
		<dt><?= __('Captain Emails') ?></dt>
		<dd><?= $this->Html->link(__('Email all coaches/captains'), 'mailto:' . implode(',', AppController::_extractEmails($captains, false, false, true))) ?></dd>
<?php
	endif;
endif;

if ($this->Authorize->can('ratings_table', $game_context)):
?>
		<dt><?= __('Ratings Table') ?></dt>
		<dd><?= $this->Html->link(__('Click to view'), ['action' => 'ratings_table', 'game' => $game->id]) ?></dd>
<?php
endif;
?>
	</dl>

<?php
$actions = [];

if ($this->Authorize->can('attendance', $game_context) && $game_context->team_id) {
	$actions[] = $this->Html->iconLink('attendance_24.png',
		['action' => 'attendance', 'team' => $game_context->team_id, 'game' => $game->id],
		['alt' => __('Attendance'), 'title' => __('View Game Attendance Report')]);
}

if ($this->Authorize->can('note', $game_context)) {
	$actions[] = $this->Html->link(__('Add Note'), ['action' => 'note', 'game' => $game->id]);
}

if ($this->Authorize->can('edit', $game)) {
	$actions[] = $this->Html->iconLink('edit_24.png',
		['action' => 'edit', 'game' => $game->id],
		['alt' => __('Edit Game'), 'title' => __('Edit Game')]);
}

if ($this->Authorize->can('delete', $game)) {
	$actions[] = $this->Form->iconPostLink('delete_24.png',
		['action' => 'delete', 'game' => $game->id],
		['alt' => __('Delete Game'), 'title' => __('Delete Game')],
		['confirm' => __('Are you sure you want to delete this game?')]);
}

if ($this->Authorize->can('stats', $game_context)) {
	$actions[] = $this->Html->iconLink('stats_24.png',
		['action' => 'stats', 'game' => $game->id],
		['alt' => __('Game Stats'), 'title' => __('Game Stats')]);
}

if (!empty($actions)) {
	echo $this->Html->tag('div',
		$this->Html->nestedList($actions, ['class' => 'nav nav-pills']),
		['class' => 'actions columns']);
}

if (array_key_exists($game->home_team_id, $game->score_entries)) {
	$homeScoreEntry = $game->score_entries[$game->home_team_id];
}
if (array_key_exists($game->away_team_id, $game->score_entries)) {
	$awayScoreEntry = $game->score_entries[$game->away_team_id];
}

if (!empty($game->spirit_entries) || Configure::read('scoring.spirit_default')) {
	$homeSpiritEntry = $game->getSpiritEntry($game->home_team_id, $spirit_obj, false, true);
	$awaySpiritEntry = $game->getSpiritEntry($game->away_team_id, $spirit_obj, false, true);
} else {
	$homeSpiritEntry = $awaySpiritEntry = false;
}
$team_names = [];
if ($game->home_team_id) {
	$team_names[$game->home_team_id] = $game->home_team->name;
}
if ($game->away_team_id) {
	$team_names[$game->away_team_id] = $game->away_team->name;
}
?>

	<fieldset class="clear-float wide-labels">
		<legend><?= __('Scoring') ?></legend>
<?php
if ($game->isFinalized()):
?>
		<dl class="dl-horizontal">
<?php
	if (!in_array($game->status, Configure::read('unplayed_status'))):
?>
			<dt><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd><?php
				echo $game->home_score;
				if ($game->division->women_present && isset($homeScoreEntry) && $homeScoreEntry->women_present) {
					echo __(' ({0})', $homeScoreEntry->women_present);
				}
			?></dd>
<?php
		if ($game->division->schedule_type != 'competition'):
?>
			<dt><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd><?php
				echo $game->away_score;
				if ($game->division->women_present && isset($awayScoreEntry) && $awayScoreEntry->women_present) {
					echo __(' ({0})', $awayScoreEntry->women_present);
				}
			?></dd>
<?php
		endif;

		if ($game->division->league->hasCarbonFlip() && $game->status == 'normal'):
?>
			<dt><?= __('Carbon Flip') ?></dt>
			<dd><?= $carbon_flip_options[$game->home_carbon_flip] ?></dd>
<?php
		endif;

		if ($show_spirit):
?>
			<dt><?= __('Spirit for {0}', $this->Text->truncate($game->home_team->name, 18)) ?></dt>
			<dd><?= $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $game->division->league,
				'show_spirit_scores' => $show_spirit_scores,
				'entry' => $awaySpiritEntry,
			]) ?></dd>
			<dt><?= __('Spirit for {0}', $this->Text->truncate($game->away_team->name, 18)) ?></dt>
			<dd><?= $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $game->division->league,
				'show_spirit_scores' => $show_spirit_scores,
				'entry' => $homeSpiritEntry,
			]) ?></dd>
<?php
		endif;
	endif;

	if ($ratings_obj->perGameRatings() && $game->type == SEASON_GAME) {
		echo $this->element("Leagues/game/{$league_obj->render_element}/score", compact('game'));
	}
?>
			<dt><?= __('Score Approved By') ?></dt>
			<dd><?php
				if ($game->approved_by_id < 0) {
					$approved = Configure::read('approved_by');
					echo __($approved[$game->approved_by_id]);
				} else {
					echo $this->element('People/block', ['person' => $game->approved_by]);
				}
			?></dd>
		</dl>

<?php
else:
?>
		<p><?= __('The score of this game has not yet been finalized.') ?></p>
<?php
	if (!empty($game->score_entries) && $this->Authorize->can('view_score_entries', $game->division)):
?>
		<h3><?= __('Score as entered') ?></h3>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= $this->Text->truncate($game->home_team->name, 23) . __(' ({0})', __('home')) ?></th>
					<th><?= $this->Text->truncate($game->away_team->name, 23) . __(' ({0})', __('away')) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= __('Home Score') ?></td>
					<td><?= isset($homeScoreEntry) ? $homeScoreEntry->score_for : __('not entered') ?></td>
					<td><?= isset($awayScoreEntry) ? $awayScoreEntry->score_against : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Away Score') ?></td>
					<td><?= isset($homeScoreEntry) ? $homeScoreEntry->score_against : __('not entered') ?></td>
					<td><?= isset($awayScoreEntry) ? $awayScoreEntry->score_for : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Defaulted?') ?></td>
					<td><?= isset($homeScoreEntry) ? ($homeScoreEntry->status == 'home_default' ? __('us') : ($homeScoreEntry->status == 'away_default' ? __('them') : __('no'))) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? ($awayScoreEntry->status == 'away_default' ? __('us') : ($awayScoreEntry->status == 'home_default' ? __('them') : __('no'))) : '' ?></td>
				</tr>
<?php
		if ($game->division->league->hasCarbonFlip()):
?>
				<tr>
					<td><?= __('Carbon Flip') ?></td>
					<td><?php
					if (isset($homeScoreEntry)) {
						if ($homeScoreEntry->status == 'normal') {
							echo $carbon_flip_options[$homeScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
					<td><?php
					if (isset($awayScoreEntry)) {
						if ($awayScoreEntry->status == 'normal') {
							echo $carbon_flip_options[$awayScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
				</tr>
<?php
		endif;

		if ($game->division->women_present):
?>
				<tr>
					<td><?= __('How many womxn designated players did you have at this game?') ?></td>
					<td><?= isset($homeScoreEntry) && $homeScoreEntry->women_present ? $homeScoreEntry->women_present : '' ?></td>
					<td><?= isset($awayScoreEntry) && $awayScoreEntry->women_present ? $awayScoreEntry->women_present : '' ?></td>
				</tr>
<?php
		endif;
?>
				<tr>
					<td><?= __('Entered By') ?></td>
					<td><?= isset($homeScoreEntry) ? $this->element('People/block', ['person' => $homeScoreEntry->person]) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? $this->element('People/block', ['person' => $awayScoreEntry->person]) : '' ?></td>
				</tr>
				<tr>
					<td><?= __('Entry Time') ?></td>
					<td><?= isset($homeScoreEntry) ? $this->Time->datetime($homeScoreEntry->modified) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? $this->Time->datetime($awayScoreEntry->modified) : '' ?></td>
				</tr>
<?php
		if ($show_spirit):
?>
				<tr>
					<td><?= __('Spirit Assigned') ?></td>
					<td><?= $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $game->division->league,
						'show_spirit_scores' => $show_spirit_scores,
						'entry' => $awaySpiritEntry,
					]) ?></td>
					<td><?= $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $game->division->league,
						'show_spirit_scores' => $show_spirit_scores,
						'entry' => $homeSpiritEntry,
					]) ?></td>
				</tr>
<?php
		endif;
?>
			</tbody>
		</table>
		</div>
<?php
	else:
		$entry = $game->getBestScoreEntry();
		if ($entry === null) {
			echo $this->Html->para(null, __('The final scores entered by the teams do not match, and the discrepancy has not been resolved.'));
		}
	endif;

	if (!empty($entry)):
?>
		<p><?php
			if ($entry->team_id === null) {
				$name = __('A scorekeeper');
			} else {
				$name = $team_names[$entry->team_id];
			}
			if ($entry->status == 'in_progress') {
					echo __('{0} reported the following in-progress score as of {1}:',
					$name, $this->Time->time($entry->modified)
				);
			} else {
				echo __('{0} reported the final score as:', $name);
			}
		?></p>
		<dl class="dl-horizontal">
			<dt><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd><?= ($entry->team_id != $game->away_team_id ? $entry->score_for : $entry->score_against) ?></dd>
			<dt><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd><?= ($entry->team_id == $game->away_team_id ? $entry->score_for : $entry->score_against) ?></dd>
		</dl>
<?php
	endif;
endif;

if (!empty($game->score_details)):
?>
		<fieldset>
			<legend><?= __('Box Score') ?></legend>
			<div id="BoxScore">
				<ul><?php
					$start = $game->game_slot->start_time;
					$scores = [$game->home_team_id => 0, $game->away_team_id => 0];

					foreach ($game->score_details as $detail) {
						$time = $detail->created->diffInMinutes($start);
						if ($detail->play == 'Start') {
							$start = $detail->created;
							$line = $this->Time->dateTime($detail->created) . ' ' . __('Game started');
							$start_text = Configure::read("sports.{$game->division->league->sport}.start.box_score");
							if ($start_text) {
								$line .= ', ' . __($start_text, $team_names[$detail->team_id]);
							}
						} else if (Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}")) {
							$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
								$team_names[$detail->team_id] . ' ' . strtolower(Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}"));
						} else {
							$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
								$team_names[$detail->team_id] . ' ' .
								strtolower($detail->play);
							if ($detail->points) {
								$scores[$detail->team_id] += $detail->points;
								$line .= __(' ({0})', implode(' - ', $scores));
							}
							$stats = [];
							foreach ($detail->score_detail_stats as $stat) {
								$stats[] = Inflector::singularize(strtolower($stat->stat_type->name)) . ' ' . $stat->person->full_name;
							}
							if (!empty($stats)) {
								$line .= __(' ({0})', implode(', ', $stats));
							}
						}
						echo $this->Html->tag('li', $line);
					}
				?></ul>
<?php
	if ($this->Authorize->can('edit_boxscore', $game)) {
		echo $this->Html->iconLink('edit_24.png',
			['action' => 'edit_boxscore', 'game' => $game->id],
			['alt' => __('Edit Box Score'), 'title' => __('Edit Box Score')]);
	}
?>
			</div>
		</fieldset>
<?php
endif;
?>
	</fieldset>

<?php
if (!in_array($game->status, Configure::read('unplayed_status'))):
	if ($show_spirit_scores) {
		echo $this->element('Spirit/view',
			['team' => $game->home_team, 'league' => $game->division->league, 'division' => $game->division, 'spirit' => $awaySpiritEntry, 'spirit_obj' => $spirit_obj]);
		echo $this->element('Spirit/view',
			['team' => $game->away_team, 'league' => $game->division->league, 'division' => $game->division, 'spirit' => $homeSpiritEntry, 'spirit_obj' => $spirit_obj]);
	}

	if ($this->Authorize->can('allstars', $game->division)):
		$allstars = collection($game->score_entries)->extract('allstars.{*}')->toArray();
		if (Configure::read('scoring.allstars') && $game->division->allstars && !empty($allstars)):
?>
	<fieldset>
		<legend><?= __('Allstars') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Player') ?></th>
					<th><?= __('Team') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			foreach ($allstars as $allstar):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $allstar]) ?></td>
					<td><?= $allstar->_joinData->team_id == $game->home_team_id ? $game->home_team->name : $game->away_team->name ?></td>
					<td class="actions"><?= $this->Html->link(__('Delete'), ['controller' => 'Allstars', 'action' => 'delete', 'allstar' => $allstar->_joinData->id], ['confirm' => __('Are you sure you want to delete this allstar?')]) ?></td>
				</tr>

<?php
			endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
		endif;

		if (Configure::read('scoring.incident_reports') && !empty($game->incidents)):
?>
	<fieldset>
		<legend><?= __('Incident Reports') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Reporting Team') ?></th>
					<th><?= __('Type') ?></th>
					<th><?= __('Details') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			foreach ($game->incidents as $incident):
?>
				<tr>
					<td><?php
					if ($game->home_team_id == $incident->team_id) {
						echo $game->home_team->name;
					} else {
						echo $game->away_team->name;
					}
					?></td>
					<td><?= $incident->type ?></td>
					<td class="spirit-incident"><?= $incident->details ?></td>
				</tr>

<?php
			endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
		endif;
	endif;
endif;

if (!empty($game->notes)):
?>
	<fieldset>
		<legend><?= __('Notes') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('From') ?></th>
					<th><?= __('Note') ?></th>
					<th><?= __('Visibility') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($game->notes as $note):
?>
				<tr>
					<td><?php
					echo $this->element('People/block', ['person' => $note->created_person]) .
						$this->Html->tag('br') .
						$this->Time->datetime($note->created) ?></td>
					<td><?= $note->note ?></td>
					<td><?= __(Configure::read("visibility.{$note->visibility}")) ?></td>
					<td class="actions"><?php
					if ($this->Authorize->getIdentity()->isMine($note)) {
						echo $this->Html->link(__('Edit'), ['action' => 'note', 'game' => $note->game_id, 'note' => $note->id]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete_note', 'note' => $note->id],
							['alt' => __('Delete'), 'title' => __('Delete Note')],
							['confirm' => __('Are you sure you want to delete this note?')]);
					}
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
endif;
?>

</div>
