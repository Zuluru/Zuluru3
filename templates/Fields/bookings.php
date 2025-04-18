<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Field $field
 */

use App\Controller\AppController;
use App\Model\Entity\GameSlot;
use Cake\Core\Configure;

$this->Breadcrumbs->add(Configure::read('UI.fields_cap'));
$this->Breadcrumbs->add(__('Availability and Bookings'));
$this->Breadcrumbs->add($field->long_name);
?>

<div class="fields bookings">
	<h2><?= __('Availability and Bookings') . ': ' . $field->long_name ?></h2>

<?php
echo $this->Selector->selector('Season', $this->Selector->extractOptionsUnsorted(
	$field->game_slots,
	function (GameSlot $item) { return collection($item->divisions)->extract('league')->toArray(); },
	'long_season'
));
echo $this->Selector->selector('Day', $this->Selector->extractOptions(
	$field->game_slots,
	function (GameSlot $item) { return collection($item->divisions)->extract('days.{*}')->toArray(); },
	'name', 'id'
));

$can_edit = $this->Authorize->can('add_game_slots', $field);
?>

	<div class="table-responsive clear-float">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Start') ?></th>
					<th><?= __('End') ?></th>
					<th><?= __('Booking') ?></th>
					<th><?= __('Available To') ?></th>
<?php
if ($can_edit):
?>
					<th><?= __('Actions') ?></th>
<?php
endif;
?>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($field->game_slots as $slot):
	$divisions = [];
	foreach ($slot->games as $game) {
		if (!array_key_exists($game->division_id, $divisions)) {
			$divisions[$game->division_id] = ['division' => $game->division, 'games' => []];
		}
		$divisions[$game->division_id]['games'][] = $this->element('Games/block', ['game' => $game, 'game_slot' => $slot, 'field' => 'id']);
	}
	$rows = max(count($divisions), 1);
?>
				<tr class="select_id_<?= $slot->id ?>">
					<td rowspan="<?= $rows ?>"><?= $this->Time->date($slot->game_date) ?></td>
<?php
	if ($this->Authorize->can('view', $slot)):
?>
					<td rowspan="<?= $rows ?>"><?= $this->Html->link($this->Time->time($slot->game_start),
						['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]]) ?></td>
<?php
	else:
?>
					<td rowspan="<?= $rows ?>"><?= $this->Time->time($slot->game_start) ?></td>
<?php
	endif;
?>
					<td rowspan="<?= $rows ?>"><?= $this->Time->time($slot->display_game_end) ?></td>
					<td><?php
						if (empty($divisions)) {
							echo '---- ' . __('open') . ' ----';
						} else {
							$division = array_shift($divisions);
							echo $this->element('Divisions/block', ['division' => $division['division'], 'field' => 'long_league_name']) .
								__(' ({0})', __n('game', 'games', count($division['games'])) . ' ' . implode(', ', $division['games']));
						}
					?></td>
					<td><?php
						$leagues = array_unique(collection($slot->divisions)->extract('long_league_name')->toArray());
						echo implode(', ', $leagues);
					?></td>
<?php
	if ($can_edit):
?>
					<td rowspan="<?= $rows ?>" class="actions"><?php
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id, 'return' => AppController::_return()]],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id, 'return' => AppController::_return()]],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this slot?')]);
					?></td>
<?php
	endif;
?>
				</tr>
<?php
	foreach ($divisions as $division):
?>
				<tr>
					<td><?= $this->element('Divisions/block', ['division' => $division['division'], 'field' => 'league_name']) .
						__(' ({0})', __n('game', 'games', count($division['games'])) . ' ' . implode(', ', $division['games']))
					?></td>
				</tr>
<?php
	endforeach;
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
