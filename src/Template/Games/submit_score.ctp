<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Submit Game Results'));

if ($team_id == $game->home_team_id) {
	$this_team = $game->home_team;
	$opponent = $game->away_team;
} else {
	$this_team = $game->away_team;
	$opponent = $game->home_team;
}
?>

<div class="games form">
	<h2><?= __('Submit Game Results') ?></h2>
	<p><?= $this->Html->para(null, __('Submit the result for the {0} game at {1} between {2} and {3}.',
		$this->Time->dateTimeRange($game->game_slot),
		$this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']),
		$this->element('Teams/block', ['team' => $this_team, 'show_shirt' => false]),
		$this->element('Teams/block', ['team' => $opponent, 'show_shirt' => false])
	)) ?></p>
	<p><?= __('If your opponent has already entered a result, it will be displayed below. If the result you enter does not agree with this result, posting of the result will be delayed until your coordinator can confirm the correct result.') ?></p>

<?php
echo $this->Form->create($game, ['align' => 'horizontal']);

echo $this->Jquery->toggleInput('score_entries.0.status', [
	'id' => 'Status',
	'label' => __('This game was:'),
	'type' => 'select',
	'options' => [
		'normal' => __('Played'),
		'home_default' => __("Defaulted by {0}", $game->home_team->name),
		'away_default' => __("Defaulted by {0}", $game->away_team->name),
		'cancelled' => __('Cancelled (e.g. due to weather)'),
	],
], [
	'values' => [
		'normal' => '.normal',
		'home_default' => '.default',
		'away_default' => '.default',
	],
]);

if (!empty($game->score_entries) && $game->score_entries[0]->has('id')) {
	echo $this->Form->hidden('score_entries.0.id', ['value' => $game->score_entries[0]->id]);
}
echo $this->Form->hidden('score_entries.0.team_id', ['value' => $team_id]);
echo $this->Form->hidden('score_entries.0.game_id', ['value' => $game->id]);
?>

	<div class="table-responsive normal default">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team Name') ?></th>
					<th><?= __('Your Score Entry') ?></th>
					<th><?= __('Opponent\'s Score Entry') ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= $this_team['name'] ?></td>
					<td><?= $this->Form->input('score_entries.0.score_for', [
						'div' => false,
						'id' => ($team_id == $game->home_team_id ? 'ScoreHome' : 'ScoreAway'),
						'label' => false,
						'type' => 'number',
						'size' => 3,
						'secure' => false,
					]) ?></td>
					<td><?php
						if ($opponent_score->person_id) {
							echo $opponent_score->score_against;
						} else {
							echo __('not yet entered');
						}
					?></td>
				</tr>
				<tr>
					<td><?= $opponent->name ?></td>
					<td><?= $this->Form->input('score_entries.0.score_against', [
						'div' => false,
						'id' => ($team_id == $game->home_team_id ? 'ScoreAway' : 'ScoreHome'),
						'label' => false,
						'type' => 'number',
						'size' => 3,
						'secure' => false,
					]) ?></td>
					<td><?php
						if ($opponent_score->person_id) {
							echo $opponent_score->score_for;
						} else {
							echo __('not yet entered');
						}
					?></td>
				</tr>
<?php
if ($game->division->league->hasCarbonFlip()):
?>
				<tr class="normal">
					<td><?= __('Carbon Flip') ?></td>
					<td><?php
						$carbon_flip_options = [
							2 => __('{0} won', $game->home_team->name),
							0 => __('{0} won', $game->away_team->name),
							1 => __('tie'),
						];
						echo $this->Form->input('score_entries.0.home_carbon_flip', [
							'div' => false,
							'id' => 'CarbonFlip',
							'label' => false,
							'empty' => '---',
							'options' => $carbon_flip_options,
							'secure' => false,
						]);
					?></td>
					<td><?php
						if ($opponent_score->person_id) {
							echo $carbon_flip_options[$opponent_score->home_carbon_flip];
						} else {
							echo __('not yet entered');
						}
					?></td>
				</tr>
<?php
endif;

if (Configure::read('scoring.gender_ratio')):
	$gender_ratio_options = Configure::read("sports.{$game->division->league->sport}.gender_ratio.{$game->division->ratio_rule}");
	if ($gender_ratio_options):
?>
				<tr class="normal">
					<td><?= __('Opponent\'s Gender Ratio') ?></td>
					<td><?php
						echo $this->Form->input('score_entries.0.gender_ratio', [
							'div' => false,
							'id' => 'GenderRatio',
							'label' => false,
							'empty' => '---',
							'options' => $gender_ratio_options,
							'secure' => false,
						]);
					?></td>
					<td></td>
				</tr>
<?php
	endif;
endif;
?>
			</tbody>
		</table>
	</div>

<?php
if ($game->division->league->hasSpirit()) {
	echo $this->element('Spirit/input', [
		'index' => 0, 'for_team' => $opponent, 'from_team' => $this_team, 'game' => $game, 'spirit_obj' => $spirit_obj
	]);
}

if (Configure::read('scoring.incident_reports')):
	echo $this->Html->tag('div', $this->Jquery->toggleInput('has_incident', [
		'type' => 'checkbox',
		'value' => '1',
		'label' => __('I have an incident to report'),
		'secure' => false,
	], [
		'selector' => '#IncidentDetails',
	]), ['class' => 'no-labels', 'style' => 'margin-top: 15px;']);

	if (!empty($game->incidents) && $game->incidents[0]->has('id')) {
		echo $this->Form->hidden('incidents.0.id', [
			'value' => $game->incidents[0]->id,
		]);
	}
	echo $this->Form->hidden('incidents.0.team_id', [
		'value' => $team_id,
	]);
?>
	<fieldset id="IncidentDetails">
		<legend>Incident Details</legend>
<?php
	echo $this->Form->input('incidents.0.type', [
		'label' => __('Incident Type'),
		'options' => Configure::read('options.incident_types'),
		'empty' => '---',
		'secure' => false,
	]);
	echo $this->Form->input('incidents.0.details', [
		'label' => __('Enter the details of the incident'),
		'cols' => 60,
		'secure' => false,
	]);
?>
	</fieldset>
<?php
endif;

if (Configure::read('scoring.allstars') && $game->division->allstars != 'never'):
?>
	<div class="normal">
<?php
	if ($game->division->allstars == 'optional') {
		echo $this->Jquery->toggleInput('has_allstars', [
			'type' => 'checkbox',
			'value' => '1',
			'label' => __('I want to nominate an all-star'),
			'secure' => false,
		], [
			'selector' => '#AllstarDetails',
		]);
	}
?>
		<fieldset id="AllstarDetails">
			<legend><?= __('Allstar Nominations') ?></legend>
			<p><?php
				echo __('You may select up to two all-stars from the list below');
				if ($game->division->allstars == 'always') {
					echo ', ' . __('if you think they deserve to be nominated as an all-star');
				}
			?>.</p>

<?php
	// Build list of allstar options
	$players = [];
	$player_roles = Configure::read('playing_roster_roles');

	if ($game->division->allstars_from == 'submitter') {
		$roster = $this_team->people;
	} else {
		$roster = $opponent->people;
	}

	foreach ($roster as $person) {
		$block = $this->element('People/block', ['person' => $person, 'link' => false]);
		if (!in_array($person->_joinData->role, $player_roles)) {
			$block .= ' (' . __('substitute') . ')';
		}
		$players[$person->id] = $block;
	}

	if (!empty($players)) {
		// TODO: Add some JavaScript to disable further entries once two are selected
		echo $this->Form->input('score_entries.0.allstars._ids', [
			'multiple' => 'checkbox',
			'options' => $players,
			'escape' => false,
			'secure' => false,
		]);
	}

	$coordinator = __('league coordinator');
	if (!empty($game->division->league->coord_list)) {
		$coordinator = $this->Html->link($coordinator, "mailto:{$game->division->league->coord_list}");
	}
?>

		<p><?= __('If you feel strongly about nominating additional all-stars, please contact your {0}.', $coordinator) ?></p>
		</fieldset>
	</div>
<?php
endif;
?>

<?php
if ($game->division->league->hasStats()):
?>
	<div class="normal">
<?php
	echo $this->Form->input('collect_stats', [
		'type' => 'checkbox',
		'value' => '1',
		'label' => __('I want to enter stats for this game (if you don\'t do it now, you can do it later)'),
		'secure' => false,
	]);
?>
	</div>
<?php
endif;
?>

	<div class="submit">
<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
<?= $this->Form->button(__('Reset'), ['type' => 'reset']) ?>
<?= $this->Form->end() ?>
	</div>
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
