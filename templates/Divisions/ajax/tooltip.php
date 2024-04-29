<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\ORM\TableRegistry;
?>
<h2><?= h($division->full_league_name) ?></h2>
<dl class="row">
<?php
if ($this->Identity->isLoggedIn() && !empty($division->people)):
	$links = [];
	foreach ($division->people as $coordinator) {
		$links[] = $this->Html->link($coordinator->full_name, ['controller' => 'People', 'action' => 'view', '?' => ['person' => $coordinator->id]]);
	}

	if (!empty($division->days)):
?>
	<dt class="col-sm-2 text-end"><?= __n('Day', 'Days', count($division->days)) ?></dt>
	<dd class="col-sm-10 mb-0"><?php
		$days = [];
		foreach ($division->days as $day) {
			$days[] = __($day->name);
		}
		echo implode(', ', $days);
	?></dd>
<?php
	endif;
?>
	<dt class="col-sm-2 text-end"><?= __('Coordinators') ?></dt>
	<dd class="col-sm-10 mb-0"><?= implode(', ', $links) ?></dd>
<?php
endif;
?>
	<dt class="col-sm-2 text-end"><?= __('Teams') ?></dt>
	<dd class="col-sm-10 mb-0"><?= count($division->teams) ?></dd>
</dl>

<p><?php
if (TableRegistry::getTableLocator()->get('Divisions')->find('byLeague', ['league' => $division->league_id])->count() == 1) {
	echo $this->Html->link(__('Details'), ['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $division->league_id]]);
} else {
	echo $this->Html->link(__('Details'), ['controller' => 'Divisions', 'action' => 'view', '?' => ['division' => $division->id]]);
}
echo ' / ' .
	$this->Html->link(__('Schedule'), ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]]) .
	' / ' .
	$this->Html->link(__('Standings'), ['controller' => 'Divisions', 'action' => 'standings', '?' => ['division' => $division->id]]);
?></p>
