<?php
use Cake\Core\Configure;
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Model\Entity\Team;
use Cake\ORM\TableRegistry;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Game') . ' ' . $game->id);
$this->Html->addCrumb(__('Edit'));

$preliminary = ($game->home_team_id === null || ($game->division->schedule_type != 'competition' && $game->away_team_id === null));
if ($preliminary) {
	$carbon_flip_options = [
		2 => __('{0} won', __('Home team')),
		0 => __('{0} won', __('Away team')),
		1 => __('tie'),
	];
} else {
	$carbon_flip_options = [
		2 => __('{0} won', $game->home_team->name),
		0 => __('{0} won', $game->away_team->name),
		1 => __('tie'),
	];
}

if (Configure::read('scoring.gender_ratio')) {
	$gender_ratio_options = Configure::read("sports.{$game->division->league->sport}.gender_ratio.{$game->division->ratio_rule}");
} else {
	$gender_ratio_options = false;
}
?>

<div class="games form">
	<h2><?= __('Edit Game') ?></h2>
	<?= $this->Form->create($game, ['align' => 'horizontal']) ?>
	<dl class="dl-horizontal">
		<dt><?= __('League') ?></dt>
		<dd><?= $this->element('Leagues/block', ['league' => $game->division->league]) ?></dd>
<?php
if (TableRegistry::get('Divisions')->find('byLeague', ['league' => $game->division->league_id])->count() != 1):
?>
		<dt><?= __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['league' => $game->division->league, 'division' => $game->division]) ?></dd>
<?php
endif;
?>
		<dt><?= __('Home Team') ?></dt>
		<dd><?php
			if ($game->home_team_id === null) {
				if ($game->has('home_dependency')) {
					echo $game->home_dependency;
				} else {
					echo __('Unassigned');
				}
				$game->home_team = new Team();
				$game->home_team->people = [];
			} else {
				echo $this->element('Teams/block', ['team' => $game->home_team]);
				if ($game->has('home_dependency')) {
					echo " ({$game->home_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo ' (' . __('currently rated') . ": {$game->home_team->rating})";
				}
			}
		?></dd>
		<dt><?= __('Away Team') ?></dt>
		<dd><?php
			if ($game->away_team_id === null) {
				if ($game->has('away_dependency')) {
					echo $game->away_dependency;
				} else {
					echo __('Unassigned');
				}
				$game->away_team = new Team();
				$game->away_team->people = [];
			} else {
				echo $this->element('Teams/block', ['team' => $game->away_team]);
				if ($game->has('away_dependency')) {
					echo " ({$game->away_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo ' (' . __('currently rated') . ": {$game->away_team->rating})";
				}
			}
		?></dd>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
		<dt><?= __('Game Status') ?></dt>
		<dd><?= $this->Jquery->toggleInput('status', [
			'id' => 'Status',
			'div' => false,
			'label' => false,
			'type' => 'select',
			'options' => Configure::read('options.game_status'),
			'empty' => '---',
		], [
			'values' => [
				'normal' => '.normal',
				'home_default' => '.default',
				'away_default' => '.default',
			],
		]);
		?></dd>
<?php
if ($game->division->schedule_type == 'roundrobin' && $game->round):
?>
		<dt><?= __('Round') ?></dt>
		<dd><?= $game->round ?></dd>
<?php
endif;

$captains = collection(array_merge($game->home_team->people, $game->away_team->people))->filter(function ($player) {
	return in_array($player->_joinData->role, Configure::read('privileged_roster_roles')) && $player->_joinData->status == ROSTER_APPROVED;
})->toArray();
if (!empty($captains)):
?>
		<dt><?= __('Coach/Captain Emails') ?></dt>
		<dd><?= $this->Html->link(__('Email all coaches and captains'), 'mailto:' . implode(',', AppController::_extractEmails($captains, false, false, true))) ?></dd>
<?php
endif;
?>
	</dl>

	<fieldset class="wide-labels normal default">
		<legend><?= __('Scoring') ?></legend>
<?php
$homeScoreEntry = $game->getScoreEntry($game->home_team_id);
$awayScoreEntry = $game->getScoreEntry($game->away_team_id);

if ($homeScoreEntry->id) {
	echo $this->Form->hidden('score_entries.0.id', [
		'value' => $homeScoreEntry->id,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField('score_entries.0.id');
} else {
	echo $this->Form->hidden('score_entries.0.game_id', [
		'value' => $homeScoreEntry->game_id,
	]);
	echo $this->Form->hidden('score_entries.0.team_id', [
		'value' => $homeScoreEntry->team_id,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField('score_entries.0.game_id');
	$this->Form->unlockField('score_entries.0.team_id');
}

if ($awayScoreEntry->id) {
	echo $this->Form->hidden('score_entries.1.id', [
		'value' => $awayScoreEntry->id,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField('score_entries.1.id');
} else {
	echo $this->Form->hidden('score_entries.1.game_id', [
		'value' => $awayScoreEntry->game_id,
	]);
	echo $this->Form->hidden('score_entries.1.team_id', [
		'value' => $awayScoreEntry->team_id,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField('score_entries.1.game_id');
	$this->Form->unlockField('score_entries.1.team_id');
}

if ($game->isFinalized()):
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$game->division->schedule_type}");
	echo $this->element("Leagues/game/{$league_obj->render_element}/score", compact('game'));
?>
		<dl class="dl-horizontal">
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
endif;

if (!empty($game->score_entries)):
?>
		<h3><?= __('Score as entered') ?></h3>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= $this->Text->truncate($game->home_team->name, 23) . ' (' . __('home') . ')' ?></th>
					<th><?= $this->Text->truncate($game->away_team->name, 23) . ' (' . __('away') . ')' ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= __('Home Score') ?></td>
					<td><?= $homeScoreEntry->person_id ? $homeScoreEntry->score_for : __('not entered') ?></td>
					<td><?= $awayScoreEntry->person_id ? $awayScoreEntry->score_against : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Away Score') ?></td>
					<td><?= $homeScoreEntry->person_id ? $homeScoreEntry->score_against : __('not entered') ?></td>
					<td><?= $awayScoreEntry->person_id ? $awayScoreEntry->score_for : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Defaulted?') ?></td>
					<td><?= $homeScoreEntry->person_id ? ($homeScoreEntry->status == 'home_default' ? __('us') : ($homeScoreEntry->status == 'away_default' ? __('them') : __('no'))) : '' ?></td>
					<td><?= $awayScoreEntry->person_id ? ($awayScoreEntry->status == 'away_default' ? __('us') : ($awayScoreEntry->status == 'home_default' ? __('them') : __('no'))) : '' ?></td>
				</tr>
<?php
	if ($game->division->league->hasCarbonFlip()):
?>
				<tr>
					<td><?= __('Carbon Flip') ?></td>
					<td><?php
					if ($homeScoreEntry->person_id) {
						if ($homeScoreEntry->status == 'normal') {
							echo $carbon_flip_options[$homeScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
					<td><?php
					if ($awayScoreEntry->person_id) {
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
?>
				<tr>
					<td><?= __('Entered By') ?></td>
					<td><?php
					if ($homeScoreEntry->person_id) {
						echo $this->element('People/block', ['person' => $homeScoreEntry->person]);
					}
					?></td>
					<td><?php
					if ($awayScoreEntry->person_id) {
						echo $this->element('People/block', ['person' => $awayScoreEntry->person]);
					}
					?></td>
				</tr>
				<tr>
					<td><?= __('Entry Time') ?></td>
					<td><?php
					if ($homeScoreEntry->person_id) {
						echo $this->Time->datetime($homeScoreEntry->modified);
					}
					?></td>
					<td><?php
					if ($awayScoreEntry->person_id) {
						echo $this->Time->datetime($awayScoreEntry->modified);
					}
					?></td>
				</tr>
<?php
	if ($game->division->league->hasSpirit()):
?>
				<tr>
					<td><?= __('Spirit Assigned') ?></td>
					<td><?= $this->element('Spirit/symbol', [
							'spirit_obj' => $spirit_obj,
							'league' => $game->division->league,
							'show_spirit_scores' => true,
							'entry' => $game->getSpiritEntry($game->away_team_id, $spirit_obj),
						]) ?></td>
					<td><?= $this->element('Spirit/symbol', [
							'spirit_obj' => $spirit_obj,
							'league' => $game->division->league,
							'show_spirit_scores' => true,
							'entry' => $game->getSpiritEntry($game->home_team_id, $spirit_obj),
						]) ?></td>
				</tr>
<?php
	endif;

	if ($gender_ratio_options):
?>
				<tr>
					<td><?= __('Opponent\'s Gender Ratio') ?></td>
					<td><?= $homeScoreEntry->gender_ratio ? $gender_ratio_options[$homeScoreEntry->gender_ratio] : '' ?></td>
					<td><?= $awayScoreEntry->gender_ratio ? $gender_ratio_options[$awayScoreEntry->gender_ratio] : '' ?></td>
				</tr>
<?php
	endif;
?>
			</tbody>
		</table>
		</div>
<?php
endif;

if (!$preliminary):
?>
		<dl class="dl-horizontal">
			<dt class="normal default"><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd class="normal default"><?= $this->Form->input('home_score', [
				'id' => 'ScoreHome',
				'label' => false,
				'size' => 2,
				'default' => (array_key_exists(null, $game->score_entries) ? $game->score_entries[null]->score_for : null),
				'secure' => false,
			]) ?></dd>
			<dt class="normal default"><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd class="normal default"><?= $this->Form->input('away_score', [
				'id' => 'ScoreAway',
				'label' => false,
				'size' => 2,
				'default' => (array_key_exists(null, $game->score_entries) ? $game->score_entries[null]->score_against : null),
				'secure' => false,
			]) ?></dd>
<?php
	if ($game->division->league->hasCarbonFlip()):
?>
			<dt class="normal"><?= __('Carbon Flip') ?></dt>
			<dd class="normal"><?= $this->Form->input('home_carbon_flip', [
				'label' => false,
				'empty' => '---',
				'options' => $carbon_flip_options,
				'selected' => array_key_exists($game->home_team_id, $game->score_entries) ? $game->score_entries[$game->home_team_id]->home_carbon_flip : (array_key_exists($game->away_team_id, $game->score_entries) ? $game->score_entries[$game->away_team_id]->home_carbon_flip : null),
				'secure' => false,
			]) ?></dd>
<?php
	endif;

	if (Configure::read('scoring.gender_ratio') && $gender_ratio_options):
?>
			<dt class="normal"><?= __('Home Gender Ratio') ?></dt>
			<dd class="normal"><?= $this->Form->input('score_entries.1.gender_ratio', [
				'label' => false,
				'empty' => '---',
				'options' => $gender_ratio_options,
				'secure' => false,
			]) ?></dd>
			<dt class="normal"><?= __('Away Gender Ratio') ?></dt>
			<dd class="normal"><?= $this->Form->input('score_entries.0.gender_ratio', [
				'label' => false,
				'empty' => '---',
				'options' => $gender_ratio_options,
				'secure' => false,
			]) ?></dd>
<?php
	endif;
?>
		</dl>
<?php
endif;
?>
	</fieldset>

<?php
if ($game->division->league->hasSpirit()) {
	echo $this->element('Spirit/input', [
		'for_team' => $game->home_team,
		'from_team' => $game->away_team,
		'game' => $game,
		'spirit_obj' => $spirit_obj,
		'index' => 0,
	]);
}

if ($game->division->allstars != 'never'):
?>
	<fieldset class="normal">
	<legend><?= __('Allstar Nominations') ?>: <?= $game->home_team->name ?></legend>

<?php
	// If the allstar submissions come from the submitting team, then the home allstars
	// are recorded under the home team's score submission, array index 0, and the away
	// allstars are recorded under the away team's score submission, array index 1.
	// Otherwise, it's the other way around.
	if ($game->division->allstars_from == 'submitter') {
		$id0 = 0;
		$id1 = 1;
	} else {
		$id0 = 1;
		$id1 = 0;
	}

	if (array_key_exists($id0, $game->score_entries)) {
		$allstars = collection($game->score_entries[$id0]->allstars)->extract('id')->toArray();
	} else {
		$allstars = [];
	}

	echo $this->Form->input("score_entries.$id0.allstars._ids", [
		'label' => false,
		'options' => collection($game->home_team->people)->combine('id', 'full_name')->toArray(),
		'multiple' => 'checkbox',
		'hiddenField' => false,
		'value' => $allstars,
		'secure' => false,
	]);
?>

	</fieldset>
<?php
endif;

if ($game->division->league->hasSpirit()) {
	echo $this->element('Spirit/input', [
		'for_team' => $game->away_team,
		'from_team' => $game->home_team,
		'game' => $game,
		'spirit_obj' => $spirit_obj,
		'index' => 1,
	]);
}

if ($game->division->allstars != 'never'):
?>
	<fieldset class="normal">
	<legend><?= __('Allstar Nominations') ?>: <?= $game->away_team->name ?></legend>

<?php
	if (array_key_exists($id1, $game->score_entries)) {
		$allstars = collection($game->score_entries[$id1]->allstars)->extract('id')->toArray();
	} else {
		$allstars = [];
	}

	echo $this->Form->input("score_entries.$id1.allstars._ids", [
		'label' => false,
		'options' => collection($game->away_team->people)->combine('id', 'full_name')->toArray(),
		'multiple' => 'checkbox',
		'hiddenField' => false,
		'value' => $allstars,
		'secure' => false,
	]);
?>

	</fieldset>
<?php
endif;
?>

	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>

</div>

<?php
// Extra handling of defaults, above and beyond what the toggle_input can do
$win = Configure::read('scoring.default_winning_score');
$lose = Configure::read('scoring.default_losing_score');
$this->Html->scriptBlock("
jQuery('#Status').on('change', function (){
	if (jQuery('#Status').val() == 'home_default') {
		jQuery('#ScoreHome').prop('readonly', true);
		jQuery('#ScoreAway').prop('readonly', true);
		jQuery('#ScoreHome').val($lose);
		jQuery('#ScoreAway').val($win);
	} else if (jQuery('#Status').val() == 'away_default') {
		jQuery('#ScoreHome').prop('readonly', true);
		jQuery('#ScoreAway').prop('readonly', true);
		jQuery('#ScoreHome').val($win);
		jQuery('#ScoreAway').val($lose);
	} else {
		jQuery('#ScoreHome').removeProp('readonly');
		jQuery('#ScoreAway').removeProp('readonly');
		if (jQuery('#Status').val() != 'normal') {
			jQuery('#ScoreHome').val(0);
			jQuery('#ScoreAway').val(0);
		}
	}
});
", ['buffer' => true]);
