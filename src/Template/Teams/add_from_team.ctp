<?php
/**
 * @type $team \App\Model\Entity\Team
 * @type $old_team \App\Model\Entity\Team
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb(__('Add Player'));
$this->Html->addCrumb($team->name);
?>

<div class="teams add_player">
	<h2><?= __('Add Player') . ': ' . $team->name ?></h2>

<?php
if (!empty($team->division_id)) {
	$old_team_name = __('{0} in {1}', $old_team->name, $old_team->division->league->long_season);
} else {
	$old_team_name = $old_team->name;
}

if (empty($old_team->people)) {
	echo $this->Html->para(null, __('All people from {0} are already on your roster.', $old_team_name));
} else {
	echo $this->Html->para(null, __('The following people were on the roster for {0} but are not on your current roster:', $old_team_name));
	echo $this->Form->create(false, ['align' => 'horizontal']);
	echo $this->Form->hidden('team', ['value' => $old_team->id]);

	$cannot = [];
	$positions = $team->division_id ? Configure::read("sports.{$team->division->league->sport}.positions") : [];

	foreach ($old_team->people as $person) {
		// TODOBOOTSTRAP: Better formatting of this list
		$label = $this->element('People/block', ['person' => $person, 'link' => false]);
		if ($person->can_add !== true) {
			$label .= ' ' . $this->Html->iconImg('help_16.png', ['title' => $this->Html->formatMessage($person->can_add, null, true), 'alt' => '?']);
		}

		$inputs = $this->Form->input("player.{$person->id}.role", [
			'label' => [
				'text' => $label,
				'escape' => false,
			],
			'type' => 'radio',
			'options' => $person->roster_role_options,
			'default' => 'none',
			'hiddenField' => false,
		]);

		if (!empty($positions)) {
			$inputs .= $this->Form->input("player.{$person->id}.position", [
				'label' => __('Position'),
				'type' => 'radio',
				'options' => $positions,
				'default' => 'unspecified',
			]);
		} else {
			$inputs .= $this->Form->hidden("player.{$person->id}.position", ['value' => 'unspecified']);
		}

		// TODO: If the team has numbers, add a field for entering that here

		if ($person->can_add === true) {
			echo $inputs;
		} else {
			$cannot[] = $inputs;
		}
	}

	if (!empty($cannot)) {
		if ($team->division_id && $team->division->is_playoff) {
			$typical_reason = __('the current roster does not meet the playoff roster rules');
		} else if (Configure::read('feature.registration')) {
			$typical_reason = __('they do not have a current membership');
		} else {
			$typical_reason = __('there is something wrong with their account');
		}
		echo $this->Html->para('warning-message',
			__('Notice: The following people are currently INELIGIBLE to participate on this roster. This is typically because {0}. They are not allowed to play with this team until this is corrected. Hover your mouse over the {1} to see the specific reason why.',
			$typical_reason,
			$this->Html->iconImg('help_16.png', ['alt' => '?'])));
		echo $this->Html->para('warning-message', __('They can still be invited to join, but will not be allowed to accept the invitation or play with your team until this is resolved.'));
		echo implode("\n", $cannot);
	}

	echo $this->Form->button(__('Invite'), ['class' => 'btn-success']);
	echo $this->Form->end();
}
?>

</div>
