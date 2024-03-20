<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Franchise $franchise
 */


use App\Authorization\ContextResource;
use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Franchises'));
$this->Breadcrumbs->add(h($franchise->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="franchises view">
	<h2><?= h($franchise->name) ?></h2>
	<dl class="dl-horizontal">
<?php
if (count($affiliates) > 1):
?>
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($franchise->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $franchise->affiliate->id]]) ?></dd>
<?php
endif;
?>
		<dt><?= __n('Owner', 'Owners', count($franchise->people)) ?></dt>
		<dd><?php
		$owners = [];
		foreach ($franchise->people as $person) {
			$owner = $this->element('People/block', compact('person'));
			if ($this->Authorize->can('remove_owner', new ContextResource($franchise, ['people' => $franchise->people]))) {
				$owner .= '&nbsp;' .
					$this->Html->tag('span',
						$this->Form->iconPostLink('delete_24.png',
							['controller' => 'Franchises', 'action' => 'remove_owner', '?' => ['franchise' => $franchise->id, 'person' => $person->id]],
							['alt' => __('Remove'), 'title' => __('Remove')]),
						['class' => 'actions']);
			}
			$owners[] = $owner;
		}
		echo implode('<br />', $owners);
		?></dd>
<?php
if (Configure::read('feature.urls') && !empty($franchise->website)):
?>
		<dt><?= __('Website') ?></dt>
		<dd><?= $this->Html->link($franchise->website, $franchise->website) ?></dd>
<?php
endif;
?>
	</dl>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if ($this->Authorize->can('add_team', $franchise)) {
	echo $this->Html->tag('li', $this->Html->iconLink('team_add_32.png',
		['action' => 'add_team', '?' => ['franchise' => $franchise->id]],
		['alt' => __('Add Team'), 'title' => __('Add Team')]));
}
if ($this->Authorize->can('edit', $franchise)) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['franchise' => $franchise->id]],
		['alt' => __('Edit'), 'title' => __('Edit Franchise')]));
}
if ($this->Authorize->can('add_owner', $franchise)) {
	echo $this->Html->tag('li', $this->Html->iconLink('move_32.png',
		['action' => 'add_owner', '?' => ['franchise' => $franchise->id]],
		['alt' => __('Add an Owner'), 'title' => __('Add an Owner')]));
}
if ($this->Authorize->can('delete', $franchise)) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['franchise' => $franchise->id]],
		['alt' => __('Delete'), 'title' => __('Delete Franchise')],
		['confirm' => __('Are you sure you want to delete this franchise?')]));
}
?>
	</ul>
</div>

<?php
if ($this->Identity->isLoggedIn()):
?>
<div class="related row">
	<div class="column">
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($franchise->teams as $team):
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team')) ?></td>
					<td><?php
					if ($team->division_id) {
						echo $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']);
					} else {
						echo __('Unassigned');
					}
					?></td>
					<td class="actions"><?php
					if ($this->Authorize->can('remove_team', $franchise)) {
							echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'remove_team', '?' => ['franchise' => $franchise->id, 'team' => $team->id]],
							['alt' => __('Remove'), 'title' => __('Remove Team from this Franchise')],
							['confirm' => __('Are you sure you want to remove this team?')]);
					}
					echo $this->element('Teams/actions', [
						'team' => $team,
						'division' => $team->division_id ? $team->division : null,
						'league' => $team->division_id ? $team->division->league : null,
					]);
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php
endif;
