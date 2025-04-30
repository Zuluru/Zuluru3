<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $officials
 * @var \App\Model\Entity\Team[] $team_officials
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\League $league
 */

if ($league->officials == OFFICIALS_NONE) {
	echo __('N/A');
	return;
}

if ($league->officials == OFFICIALS_TEAM && !empty($team_officials)) {
	// @todo: Current assumption is that only one team will be assigned. If that changes, we need to blend the two arrays
	echo $this->element('Teams/block', ['team' => $team_officials[0], 'show_shirt' => false]) . ': ';
}

$output = [];
foreach ($officials as $official) {
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
