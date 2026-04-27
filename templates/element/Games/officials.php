<?php
declare(strict_types=1);

use Cake\Core\Configure;

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $assigned_officials
 * @var \App\Model\Entity\Team[] $team_officials
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\League $league
 * @var array $officials
 * @var bool $edit_officials
 */

$asmselect = Configure::read('GamesOfficialsAsmSelectTracker') ?: 0;

if ($league->officials == OFFICIALS_NONE) {
	echo __('N/A');
	return;
}

if ($league->officials == OFFICIALS_TEAM && !empty($team_officials)) {
	// @todo: Current assumption is that only one team will be assigned. If that changes, we need to blend the two arrays
	echo $this->element('Teams/block', ['team' => $team_officials[0], 'show_shirt' => false]) . ': ';
}

if (isset($edit_officials) && $edit_officials && $league->officials == OFFICIALS_ADMIN) {
	echo $this->Form->control("games.{$game->id}.officials._ids", [
		'label' => false,
		'options' => $officials,
		'multiple' => true,
		'hiddenField' => false,
		'title' => __('Select the official(s) for the selected game(s)'),
	]);
	if ($this->Form->hasFormProtector()) {
		$this->Form->unlockField("asmSelect{$asmselect}");
		$this->Form->unlockField("games.{$game->id}.officials._ids");
		Configure::write('GamesOfficialsAsmSelectTracker', ++$asmselect);
	}

	return;
}

$output = [];
foreach ($assigned_officials as $official) {
	$output[] = $this->Html->link($official->full_name, ['controller' => 'People', 'action' => 'officiating_schedule', '?' => ['official' => $official->id]]);
}

if (!empty($output)) {
	echo implode(', ', $output);
} else {
	echo __('Unassigned');
}

if ($league->officials == OFFICIALS_TEAM && !empty($team_officials) && $this->Authorize->can('assign_official', $team_officials[0])) {
	echo ' (' . $this->Html->link(__('Assign now') , ['controller' => 'Games', 'action' => 'assign_official', '?' => ['game' => $game->id]]) . ')';
}
