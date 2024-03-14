<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GameSlot $game_slot
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Submit Game Results'));
?>

<div class="games form">
	<h2><?= __('Submit Game Results') ?></h2>

	<p>Submit the results for the <?= $this->Time->date($game_slot->game_date) . ' ' .
		$this->Time->time($game_slot->game_start)
	?> at <?= $game_slot->field->long_name ?>.</p>

<?php
echo $this->Form->create($game, ['align' => 'horizontal']);

echo $this->Form->control("Game.status", [
	'id' => 'Status',
	'label' => __('This game was:'),
	'options' => [
		'normal' => 'Played',
		'cancelled' => 'Cancelled (e.g. due to weather)',
	],
]);
?>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed" id="Scores">
			<thead>
				<tr>
					<th>Team Name</th>
					<th>Score</th>
<?php
if (Configure::read('scoring.incident_reports')):
?>
					<th>Incident</th>
<?php
endif;
?>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($game_slot->games as $game):
?>
				<tr>
					<td><?= $game['HomeTeam']['name'] ?></td>
					<td><?= $this->Form->control("Game.{$game['home_team_id']}.home_score", [
						'div' => false,
						'class' => 'score',
						'label' => false,
						'type' => 'number',
						'size' => 3,
					]) ?></td>
<?php
	if (Configure::read('scoring.incident_reports')):
?>
					<td><?php
						echo $this->Form->control("Game.{$game['home_team_id']}.incident", [
							'class' => 'incident_checkbox',
							'type' => 'checkbox',
							'value' => '1',
							'label' => false,
						]);
						echo $this->Form->hidden("Game.{$game['home_team_id']}.type");
						echo $this->Form->hidden("Game.{$game['home_team_id']}.details");
						$this->Html->scriptBlock("zjQuery('#Game{$game['home_team_id']}Incident').data('team_id', {$game['home_team_id']});", ['buffer' => true]);
					?></td>
<?php
	endif;
?>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>

	<div class="submit">
		<?= $this->Form->button('Submit', ['class' => 'btn-success']) ?>
		<?= $this->Form->button('Reset', ['type' => 'reset']) ?>
		<?= $this->Form->end() ?>
	</div>

<?php
if (Configure::read('scoring.incident_reports')):
?>
	<div id="IncidentDialog" title="Incident Details" class="form">
		<div class="zuluru">
			<form>
<?php
	echo $this->Form->hidden('Incident.team');
	echo $this->Form->control('Incident.type', [
		'label' => __('Incident Type'),
		'options' => Configure::read('options.incident_types'),
		'empty' => '---',
	]);
	echo $this->Form->control('Incident.details', [
		'label' => __('Enter the details of the incident'),
		'cols' => 60,
	]);
?>
			</form>
		</div>
	</div>
<?php
endif;
?>

</div>

<?php
$this->Html->scriptBlock("
function statusChanged() {
	if (zjQuery('#Status').val() == 'normal') {
		enableCommon();
		enableScores();
	} else {
		zjQuery('.score').val(0);
		disableCommon();
		disableScores();
	}
}

function disableScores() {
	zjQuery('#Scores').css('display', 'none');
}

function enableScores() {
	zjQuery('#Scores').css('display', '');
}

function disableCommon() {
	zjQuery('input:text').prop('disabled', true);
	zjQuery('input[type=\"number\"]').prop('disabled', true);
	zjQuery('.incident_checkbox').prop('disabled', true);
}

function enableCommon() {
	zjQuery('input:text').prop('disabled', false);
	zjQuery('input[type=\"number\"]').prop('disabled', false);
	zjQuery('.incident_checkbox').prop('disabled', false);
}

function incidentCheckboxChanged(checkbox) {
	var team = checkbox.data('team_id');
	if (checkbox.prop('checked')) {
		zjQuery('#IncidentTeam').val(team);
		zjQuery('#IncidentType').val(zjQuery('#Game' + team + 'Type').val());
		zjQuery('#IncidentDetails').val(zjQuery('#Game' + team + 'Details').val());
		zjQuery('#IncidentDialog').dialog('open');
	}
}

function updateIncident() {
	var team = zjQuery('#IncidentTeam').val();
	zjQuery('#Game' + team + 'Type').val(zjQuery('#IncidentType').val());
	zjQuery('#Game' + team + 'Details').val(zjQuery('#IncidentDetails').val());
}
", ['buffer' => true]);

// Make sure things are set up correctly, in the case that
// invalid data was detected and the form re-displayed.
$continue = __('Continue');
$cancel = __('Cancel');
$this->Html->scriptBlock("
zjQuery('#Status').on('change', function (){statusChanged();});
zjQuery('.incident_checkbox').on('change', function (){incidentCheckboxChanged(zjQuery(this));});
zjQuery('#IncidentDialog').dialog({
		autoOpen: false,
		buttons: {
			'$continue': function () {
				zjQuery(this).dialog('close');
				updateIncident();
			},
			'$cancel': function () { zjQuery(this).dialog('close'); }
		},
		modal: true,
		resizable: false,
		width: 500
});
statusChanged();
", ['buffer' => true]);
