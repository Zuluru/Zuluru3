<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\League $league
 */

use App\Model\Table\TeamsTable;
use App\Model\Entity\Division;

$tournaments = collection($league->divisions)->every(function (Division $division) {
	return $division->schedule_type == 'tournament';
});
$this->Breadcrumbs->add($tournaments ? __('Tournaments') : __('Leagues'));
$this->Breadcrumbs->add($league->full_name);
$this->Breadcrumbs->add(__('Participation'));
?>

<div class="leagues index">
	<h2><?= __('Participation') . ': ' . $league->full_name ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Person') ?></th>
					<th><?= __('Role') ?></th>
					<th><?= __('Date') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($league->divisions as $division):
	if (count($league->divisions) > 1):
?>
				<tr>
					<td colspan="4"><h3><?= $division->name ?></h3></td>
				</tr>
<?php
	endif;

	foreach ($division->teams as $team):
		$team_name = $this->element('Teams/block', compact('team'));
		usort($team->people, [TeamsTable::class, 'compareRoster']);
		foreach ($team->people as $person):
?>
					<tr>
						<td><?= $team_name ?></td>
						<td><?= $this->element('People/block', compact('person')) ?></td>
						<td><?= $this->element('People/roster_role', ['roster' => $person->_joinData, 'team' => $team, 'division' => $division]) ?></td>
						<td><?= $this->Time->date($person->_joinData->created) ?></td>
					</tr>
<?php
			$team_name = null;
		endforeach;
	endforeach;
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>

<div class="actions columns"><?= $this->element('Leagues/actions', [
	'league' => $league,
	'format' => 'list',
	'tournaments' => $tournaments,
	'extra_links' => $this->Html->link(__('Download {0} List', 'Participation'), ['action' => 'participation', 'league' => $league->id, '_ext' => 'csv']),
]) ?></div>
<?= $this->element('People/roster_div');
