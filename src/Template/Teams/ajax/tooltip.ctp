<?php

use App\Authorization\ContextResource;
use Cake\Core\Configure;
?>

<h2><?php
if (Configure::read('feature.team_logo') && !empty($team->logo)) {
	echo $this->Html->image($team->logo) . ' ';
}
echo $team->name;
?></h2>
<dl class="dl-horizontal">
<?php
if (Configure::read('feature.shirt_colour') && !empty($team->shirt_colour)):
?>
	<dt><?= __('Shirt Colour') ?></dt>
	<dd><?= $team->shirt_colour ?></dd>
<?php
endif;

if ($this->Authorize->can('view_roster', \App\Controller\TeamsController::class) && !empty($team->people)):
	$all_captains = array_fill_keys(Configure::read('privileged_roster_roles'), []);
	foreach ($team->people as $person) {
		$all_captains[$person->_joinData->role][] = $person;
	}
	$links = [];
	foreach ($all_captains as $role => $captains) {
		foreach ($captains as $captain) {
			$link = $this->Html->link($captain->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $captain->id]);
			if ($role == 'assistant') {
				$link .= ' (A)';
			}
			$links[] = $link;
		}
	}
?>
	<dt><?= __('Coaches/Captains') ?></dt>
	<dd><?= implode(', ', $links) ?></dd>
<?php
endif;
?>

	<dt><?= __('Team') ?></dt>
	<dd><?php
		echo $this->Html->link(__('Details & roster'), ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		if (!empty($team->division_id)) {
			echo ' / ' .
				$this->Html->link(__('Schedule'), ['controller' => 'Teams', 'action' => 'schedule', 'team' => $team->id]) .
				' / ' .
				$this->Html->link(__('Standings'), ['controller' => 'Divisions', 'action' => 'standings', 'division' => $team->division_id, 'team' => $team->id]);
			if ($this->Authorize->can('stats', new ContextResource($team, ['league' => $team->division_id ? $team->division->league : null]))) {
				echo ' / ' . $this->Html->link(__('Stats'), ['controller' => 'Teams', 'action' => 'stats', 'team' => $team->id]);
			}
		}
		if (Configure::read('feature.urls') && !empty($team->website)) {
			echo ' / ' . $this->Html->link(__('Website'), $team->website);
		}
	?></dd>

<?php
if (!empty($team->division_id)):
?>
	<dt><?= __('Division') ?></dt>
	<dd><?php
		$title = ['title' => $team->division->full_league_name];
		echo $this->Html->link(__('Details'), ['controller' => 'Divisions', 'action' => 'view', 'division' => $team->division_id], $title) .
			' / ' .
			$this->Html->link(__('Schedule'), ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $team->division_id]) .
			' / ' .
			$this->Html->link(__('Standings'), ['controller' => 'Divisions', 'action' => 'standings', 'division' => $team->division_id]);
	?></dd>
<?php
endif;

if (!empty($team->notes)):
?>
	<dt><?= __('Notes') ?></dt>
	<dd><?php
		foreach ($team->notes as $note) {
			echo $note->note;
		}
	?></dd>
<?php
endif;
?>

</dl>
