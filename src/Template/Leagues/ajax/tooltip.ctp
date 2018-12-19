<h2><?= $league->full_name ?></h2>
<dl class="dl-horizontal">
	<dt><?= __('Season') ?></dt>
	<dd><?= __($league->season) ?></dd>
<?php
if (count($league->divisions) == 1):
	if (!empty($league->divisions[0]->people) && $this->Authorize->getIdentity()->isLoggedIn()):
		$links = [];
		foreach ($league->divisions[0]->people as $coordinator) {
			$links[] = $this->Html->link($coordinator->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $coordinator->id]);
		}
?>
	<dt><?= __('Coordinators') ?></dt>
	<dd><?= implode(', ', $links) ?></dd>
<?php
	endif;
?>
	<dt><?= __('Teams') ?></dt>
	<dd><?= count($league->divisions[0]->teams) ?></dd>
<?php
else:
	foreach ($league->divisions as $division):
?>
	<dt><?php
		if (strlen($division->name) > 12) {
			echo $this->Html->tag('span', $this->Text->truncate($division->name, 12), ['title' => $division->name]);
		} else {
			echo $division->name;
		}
	?>&nbsp;</dt>
	<dd><?= $this->Html->link(__('Details'), ['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]) .
		' / ' .
		$this->Html->link(__('Schedule'), ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division->id]) .
		' / ' .
		$this->Html->link(__('Standings'), ['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id]);
	?></dd>
<?php
	endforeach;
endif;
?>

</dl>

<p><?php
	echo $this->Html->link(__('Details'), ['controller' => 'Leagues', 'action' => 'view', 'league' => $league->id]);
	if (count($league->divisions) == 1) {
		echo ' / ' .
			$this->Html->link(__('Schedule'), ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $league->divisions[0]->id]) .
			' / ' .
			$this->Html->link(__('Standings'), ['controller' => 'Divisions', 'action' => 'standings', 'division' => $league->divisions[0]->id]);
	}
?></p>
