<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__(Configure::read('UI.fields_cap')));
$this->Html->addCrumb(__('Availability and Bookings'));
$this->Html->addCrumb($field->long_name);
?>

<div class="fields bookings">
	<h2><?= __('Availability and Bookings') . ': ' . $field->long_name ?></h2>

<?php
$seasons = array_unique(collection($field->game_slots)->extract('divisions.{*}.league.long_season')->toList());
echo $this->element('selector', [
	'title' => 'Season',
	'options' => $seasons,
]);
$days = collection($field->game_slots)->extract('divisions.{*}.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
echo $this->element('selector', [
	'title' => 'Day',
	'options' => $days,
]);
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
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
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
	$seasons = array_unique(collection($slot->games)->extract('division.league.long_season')->toArray());
	$day = $slot->game_date->format('l');

	$divisions = [];
	foreach ($slot->games as $game) {
		if (!array_key_exists($game->division_id, $divisions)) {
			$divisions[$game->division_id] = ['division' => $game->division, 'games' => []];
		}
		$divisions[$game->division_id]['games'][] = $this->element('Games/block', ['game' => $game, 'game_slot' => $slot, 'field' => 'id']);
	}
	$rows = max(count($divisions), 1);
?>
				<tr class="<?= $this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $day]) ?>">
					<td rowspan="<?= $rows ?>"><?= $this->Time->date($slot->game_date) ?></td>
<?php
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
?>
					<td rowspan="<?= $rows ?>"><?= $this->Html->link($this->Time->time($slot->game_start),
						['controller' => 'GameSlots', 'action' => 'view', 'slot' => $slot->id]) ?></td>
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
								' (' . __n('game', 'games', count($division['games'])) . ' ' . implode(', ', $division['games']) . ')';
						}
					?></td>
					<td><?php
						$leagues = array_unique(collection($slot->divisions)->extract('long_league_name')->toArray());
						echo implode(', ', $leagues);
					?></td>
<?php
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
?>
					<td rowspan="<?= $rows ?>" class="actions"><?php
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'GameSlots', 'action' => 'edit', 'slot' => $slot->id, 'return' => AppController::_return()],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'GameSlots', 'action' => 'delete', 'slot' => $slot->id, 'return' => AppController::_return()],
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
						' (' . __n('game', 'games', count($division['games'])) . ' ' . implode(', ', $division['games']) . ')'
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
