<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var string[] $officials
 * @var string[] $teams
 * @var \App\Module\Spirit $spirit_obj
 */

use App\Service\Games\ScoreService;
use App\Service\Games\SpiritService;
use Cake\Core\Configure;
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Model\Entity\Team;
use Cake\ORM\TableRegistry;

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Game') . ' ' . $game->id);
$this->Breadcrumbs->add(__('Edit'));

$preliminary = ($game->home_team_id === null || ($game->division->schedule_type !== 'competition' && $game->away_team_id === null));
if ($game->division->league->hasCarbonFlip()) {
	if ($preliminary) {
		$carbon_flip_options = [
			2 => __('{0} won', __('Home Team')),
			0 => __('{0} won', __('Away Team')),
			1 => __('tie'),
		];
	} else {
		$carbon_flip_options = [
			2 => __('{0} won', $game->home_team->name),
			0 => __('{0} won', $game->away_team->name),
			1 => __('tie'),
		];
	}
}
?>

<div class="games form">
	<h2><?= __('Edit Game') ?></h2>
	<?= $this->Form->create($game, ['align' => 'horizontal']) ?>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('League') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Leagues/block', ['league' => $game->division->league]) ?></dd>
<?php
if (TableRegistry::getTableLocator()->get('Divisions')->find('byLeague', ['league' => $game->division->league_id])->count() != 1):
?>
		<dt class="col-sm-3 text-end"><?= __('Division') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Divisions/block', ['league' => $game->division->league, 'division' => $game->division]) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Home Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
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
				if ($game->division->schedule_type !== 'tournament') {
					echo __(' ({0})', __('currently rated: {0}', $game->home_team->rating));
				}
			}
		?></dd>
<?php
if ($game->division->schedule_type !== 'competition'):
?>
		<dt class="col-sm-3 text-end"><?= __('Away Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
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
				if ($game->division->schedule_type !== 'tournament') {
					echo __(' ({0})', __('currently rated: {0}', $game->away_team->rating));
				}
			}
		?></dd>
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Date and Time') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Location') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
<?php
if (Configure::read('feature.officials')):
	if ($game->division->league->officials == OFFICIALS_ADMIN):
?>
		<dt class="col-sm-3 text-end"><?= __('Officials') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
			echo $this->Form->control('officials._ids', [
				'label' => false,
				'options' => $officials,
				'multiple' => true,
				'hiddenField' => false,
				'title' => __('Select the officials for this game'),
				'secure' => false,
			]);
			if ($this->Form->hasFormProtector()) {
				$this->Form->unlockField('asmSelect0');
				$this->Form->unlockField('officials._ids');
			}
			$this->Html->css('jquery.asmselect.css', ['block' => true]);
			$this->Html->script('jquery.asmselect.js', ['block' => true]);
			$this->Html->scriptBlock('zjQuery("select[multiple]").asmSelect({sortable:true});', ['buffer' => true]);
		?></dd>
<?php
	elseif ($game->division->league->officials == OFFICIALS_TEAM):
?>
		<dt class="col-sm-3 text-end"><?= __('Officials From') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
			echo $this->Form->control('team_officials._ids.0', [
				'label' => false,
				'type' => 'select',
				'options' => $teams,
				'empty' => '---',
				'title' => __('Select the team that will be providing officials for this game'),
			]);
		?></dd>
<?php
	endif;
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Game Status') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Jquery->toggleInput('status', [
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
if ($game->division->schedule_type === 'roundrobin' && $game->round):
?>
		<dt class="col-sm-3 text-end"><?= __('Round') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $game->round ?></dd>
<?php
endif;

if ($game->home_team) {
	$captains = $game->home_team->people;
} else {
	$captains = [];
}
if ($game->away_team) {
	$captains = array_merge($captains, $game->away_team->people);
}
$captains = collection($captains)->filter(function ($player) {
	return in_array($player->_joinData->role, Configure::read('privileged_roster_roles')) && $player->_joinData->status == ROSTER_APPROVED;
})->toArray();
if (!empty($captains)):
?>
		<dt class="col-sm-3 text-end"><?= __('Coach/Captain Emails') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link(__('Email all coaches and captains'), 'mailto:' . implode(',', AppController::_extractEmails($captains, false, false, true))) ?></dd>
<?php
endif;
?>
	</dl>

	<fieldset class="normal default">
		<legend><?= __('Scoring') ?></legend>
<?php
if ($game->isFinalized()):
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$game->division->schedule_type}");
?>
		<dl class="row">
<?php
	echo $this->element("Leagues/game/{$league_obj->render_element}/score", compact('game'));
?>
			<dt class="col-sm-3 text-end"><?= __('Score Approved By') ?></dt>
			<dd class="col-sm-9 mb-0"><?php
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

$score_service = new ScoreService($game->score_entries ?? []);
$homeScoreEntry = $score_service->getScoreEntryFrom($game->home_team_id);
$awayScoreEntry = $score_service->getScoreEntryFrom($game->away_team_id);
if (!empty($game->score_entries)):
?>
		<h3><?= __('Score as entered') ?></h3>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= $this->Text->truncate($game->home_team->name, 23) . __(' ({0})', __('home')) ?></th>
<?php
	if ($game->division->schedule_type !== 'competition'):
?>
					<th><?= $this->Text->truncate($game->away_team->name, 23) . __(' ({0})', __('away')) ?></th>
<?php
	endif;
?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= __('Home Score') ?></td>
					<td><?= $homeScoreEntry && $homeScoreEntry->person_id ? $homeScoreEntry->score_for : __('not entered') ?></td>
<?php
	if ($game->division->schedule_type !== 'competition'):
?>
					<td><?= $awayScoreEntry && $awayScoreEntry->person_id ? $awayScoreEntry->score_against : __('not entered') ?></td>
<?php
	endif;
?>
				</tr>
<?php
	if ($game->division->schedule_type !== 'competition'):
?>
				<tr>
					<td><?= __('Away Score') ?></td>
					<td><?= $homeScoreEntry && $homeScoreEntry->person_id ? $homeScoreEntry->score_against : __('not entered') ?></td>
					<td><?= $awayScoreEntry && $awayScoreEntry->person_id ? $awayScoreEntry->score_for : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Defaulted?') ?></td>
					<td><?= $homeScoreEntry && $homeScoreEntry->person_id ? ($homeScoreEntry->status == 'home_default' ? __('us') : ($homeScoreEntry->status == 'away_default' ? __('them') : __('no'))) : '' ?></td>
					<td><?= $awayScoreEntry && $awayScoreEntry->person_id ? ($awayScoreEntry->status == 'away_default' ? __('us') : ($awayScoreEntry->status == 'home_default' ? __('them') : __('no'))) : '' ?></td>
				</tr>
<?php
	endif;

	if ($game->division->league->hasCarbonFlip()):
?>
				<tr>
					<td><?= __('Carbon Flip') ?></td>
					<td><?php
					if ($homeScoreEntry && $homeScoreEntry->person_id) {
						if ($homeScoreEntry->status === 'normal') {
							echo $carbon_flip_options[$homeScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
					<td><?php
					if ($awayScoreEntry && $awayScoreEntry->person_id) {
						if ($awayScoreEntry->status === 'normal') {
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
					if ($homeScoreEntry && $homeScoreEntry->person_id) {
						echo $this->element('People/block', ['person' => $homeScoreEntry->person]);
					}
					?></td>
					<td><?php
					if ($awayScoreEntry && $awayScoreEntry->person_id) {
						echo $this->element('People/block', ['person' => $awayScoreEntry->person]);
					}
					?></td>
				</tr>
				<tr>
					<td><?= __('Entry Time') ?></td>
					<td><?php
					if ($homeScoreEntry && $homeScoreEntry->person_id && $homeScoreEntry->modified) {
						echo $this->Time->datetime($homeScoreEntry->modified);
					}
					?></td>
					<td><?php
					if ($awayScoreEntry && $awayScoreEntry->person_id && $awayScoreEntry->modified) {
						echo $this->Time->datetime($awayScoreEntry->modified);
					}
					?></td>
				</tr>
<?php
	if ($game->division->league->hasSpirit()):
		$spirit_service = new SpiritService($game->spirit_entries ?? [], $spirit_obj);
?>
				<tr>
					<td><?= __('Spirit Assigned') ?></td>
					<td><?= $this->element('Spirit/symbol', [
							'spirit_obj' => $spirit_obj,
							'show_spirit_scores' => true,
							'value' => $spirit_service->getScoreFor($game->home_team_id, $game->division->league),
						]) ?></td>
					<td><?= $this->element('Spirit/symbol', [
							'spirit_obj' => $spirit_obj,
							'show_spirit_scores' => true,
							'value' => $spirit_service->getScoreFor($game->away_team_id, $game->division->league),
						]) ?></td>
				</tr>
<?php
	endif;

	if ($game->division->women_present):
?>
				<tr>
					<td><?= __('How many women designated players did you have at this game?') ?></td>
					<td><?= $homeScoreEntry ? $homeScoreEntry->women_present : '' ?></td>
					<td><?= $awayScoreEntry ? $awayScoreEntry->women_present : '' ?></td>
				</tr>
<?php
	endif;
?>
			</tbody>
		</table>
		</div>
<?php
endif;

if (!$preliminary && $game->game_slot->start_time->isPast()):
	if ($homeScoreEntry && $homeScoreEntry->id) {
		echo $this->Form->hidden('score_entries.0.id', [
			'value' => $homeScoreEntry->id,
		]);
		// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
		if ($this->Form->hasFormProtector()) {
			$this->Form->unlockField('score_entries.0.id');
		}
	} else {
		echo $this->Form->hidden('score_entries.0.game_id', [
			'value' => $game->id,
		]);
		echo $this->Form->hidden('score_entries.0.team_id', [
			'value' => $game->home_team_id,
		]);
		// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
		if ($this->Form->hasFormProtector()) {
			$this->Form->unlockField('score_entries.0.game_id');
			$this->Form->unlockField('score_entries.0.team_id');
		}
	}

	if ($awayScoreEntry && $awayScoreEntry->id) {
		echo $this->Form->hidden('score_entries.1.id', [
			'value' => $awayScoreEntry->id,
		]);
		// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
		if ($this->Form->hasFormProtector()) {
			$this->Form->unlockField('score_entries.1.id');
		}
	} else if ($game->away_team_id) {
		echo $this->Form->hidden('score_entries.1.game_id', [
			'value' => $game->id,
		]);
		echo $this->Form->hidden('score_entries.1.team_id', [
			'value' => $game->away_team_id,
		]);
		// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
		if ($this->Form->hasFormProtector()) {
			$this->Form->unlockField('score_entries.1.game_id');
			$this->Form->unlockField('score_entries.1.team_id');
		}
	}
?>
		<dl class="row">
			<dt class="col-sm-3 text-end normal default"><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd class="col-sm-9 mb-0 normal default"><?= $this->Form->control('home_score', [
				'id' => 'ScoreHome',
				'label' => false,
				'size' => 2,
				'default' => (array_key_exists(null, $game->score_entries) ? $game->score_entries[null]->score_for : null),
				'secure' => false,
			]) ?></dd>
<?php
	if ($game->division->schedule_type !== 'competition'):
?>
			<dt class="col-sm-3 text-end normal default"><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd class="col-sm-9 mb-0 normal default"><?= $this->Form->control('away_score', [
				'id' => 'ScoreAway',
				'label' => false,
				'size' => 2,
				'default' => (array_key_exists(null, $game->score_entries) ? $game->score_entries[null]->score_against : null),
				'secure' => false,
			]) ?></dd>
<?php
	else:
		// TODO: Instead, make it accept no score for competition games
		echo $this->Form->hidden('away_score', [
			'value' => 0,
		]);
	endif;

	if ($game->division->league->hasCarbonFlip()):
?>
			<dt class="col-sm-3 text-end normal"><?= __('Carbon Flip') ?></dt>
			<dd class="col-sm-9 mb-0 normal"><?= $this->Form->control('home_carbon_flip', [
				'label' => false,
				'empty' => '---',
				'options' => $carbon_flip_options,
				'selected' => array_key_exists($game->home_team_id, $game->score_entries) ? $game->score_entries[$game->home_team_id]->home_carbon_flip : (array_key_exists($game->away_team_id, $game->score_entries) ? $game->score_entries[$game->away_team_id]->home_carbon_flip : null),
				'secure' => false,
			]) ?></dd>
<?php
	endif;

	if ($game->division->women_present):
?>
			<dt class="col-sm-3 text-end normal"><?= __('Home Team Women Designated Players') ?></dt>
			<dd class="col-sm-9 mb-0 normal"><?= $this->Form->control('score_entries.0.women_present', [
				'label' => false,
				'secure' => false,
			]) ?></dd>
			<dt class="col-sm-3 text-end normal"><?= __('Away Team Women Designated Players') ?></dt>
			<dd class="col-sm-9 mb-0 normal"><?= $this->Form->control('score_entries.1.women_present', [
				'label' => false,
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

	echo $this->Form->control("score_entries.$id0.allstars._ids", [
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

	echo $this->Form->control("score_entries.$id1.allstars._ids", [
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
zjQuery('#Status').on('change', function (){
	if (zjQuery('#Status').val() == 'home_default') {
		zjQuery('#ScoreHome').prop('readonly', true);
		zjQuery('#ScoreAway').prop('readonly', true);
		zjQuery('#ScoreHome').val($lose);
		zjQuery('#ScoreAway').val($win);
	} else if (zjQuery('#Status').val() == 'away_default') {
		zjQuery('#ScoreHome').prop('readonly', true);
		zjQuery('#ScoreAway').prop('readonly', true);
		zjQuery('#ScoreHome').val($win);
		zjQuery('#ScoreAway').val($lose);
	} else {
		zjQuery('#ScoreHome').removeProp('readonly');
		zjQuery('#ScoreAway').removeProp('readonly');
		if (zjQuery('#Status').val() != 'normal') {
			zjQuery('#ScoreHome').val(0);
			zjQuery('#ScoreAway').val(0);
		}
	}
});
", ['buffer' => true]);
