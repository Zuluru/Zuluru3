<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $attendance
 */

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Table\StatsTable;

if (!$attendance->track_attendance) {
	echo $this->Html->para('warning-message', __('Because this team does not have attendance tracking enabled, the list below assumes that all regular players are attending and all subs are unknown. To enable attendance tracking, {0}.',
		$this->Html->link(__('edit the team record'), ['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $attendance->id]])));
}

$style = 'width:' . floor(80 / count($game->division->league->stat_types)) . '%;';
$stats_table = TableRegistry::getTableLocator()->get('Stats');
?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed" id="team_<?= $attendance->id ?>">
		<thead>
			<tr>
				<th><?= __('Player') ?></th>
				<th class="attendance_details"><?= __('Attendance') ?></th>
<?php
foreach ($stat_types as $stat) {
	echo $this->Html->tag('th', __($stat->name), compact('style'));
}
?>

			</tr>
		</thead>
		<tbody>
<?php
foreach ($attendance->people as $person):
	if (!empty($person->attendances)):
		$record = $person->attendances[0];
?>

			<tr class="<?= $record->status == ATTENDANCE_ATTENDING ? '' : 'attendance_details' ?>">
				<td><?= $this->element('People/block', compact('person')) ?></td>
				<td class="attendance_details"><?php
					echo $this->element('Games/attendance_change', [
						'team' => $attendance,
						'game' => $game,
						'person_id' => $person->id,
						'role' => $person->_joinData->role,
						'attendance' => $record,
						'dedicated' => true,
						// We need to display this even if teams have attendance tracking off
						'force' => true,
					]);
				?></td>
<?php
		foreach ($stat_types as $stat):
?>
				<td style="<?= $style ?>">
<?php
			$i = \App\lib\fake_id();
			$stat_record = collection($game->stats)->firstMatch(['team_id' => $attendance->id, 'person_id' => $person->id, 'stat_type_id' => $stat->id]);
			if (!empty($stat_record)) {
				if (!empty($stat_record->id)) {
					echo $this->Form->hidden("stats.$i.id", ['value' => $stat_record->id]);
				}
			} else {
				$stat_record = $stats_table->newEntity([
					'game_id' => $game->id,
					'team_id' => $attendance->id,
					'person_id' => $person->id,
					'stat_type_id' => $stat->id,
					'value' => null,
				]);
			}
			echo $this->Form->hidden("stats.$i.game_id", ['value' => $stat_record->game_id]);
			echo $this->Form->hidden("stats.$i.team_id", ['value' => $stat_record->team_id]);
			echo $this->Form->hidden("stats.$i.person_id", ['value' => $stat_record->person_id]);
			echo $this->Form->hidden("stats.$i.stat_type_id", ['value' => $stat_record->stat_type_id]);

			$class = "stat_{$stat->id}";
			// If there's no position for this person, or the stat is applicable to their position, or there's already
			// data for it, we consider it to be applicable. Otherwise, no.
			if (!empty($person->_joinData->position) && !StatsTable::applicable($stat, $person->_joinData->position) && empty($stat_record->value)) {
				$class .= ' unapplicable';
			}
			echo $this->Form->control("stats.$i.value", ['div' => false, 'label' => false, 'size' => 3, 'type' => 'number', 'class' => $class, 'data-stat-id' => $stat->id, 'value' => $stat_record->value]);
?>
				</td>
<?php
		endforeach;
?>

			</tr>
<?php
	endif;
endforeach;

// TODO: Add this feature
if (0 && $attendance->track_attendance):
?>

			<tr id="add_row_<?= $attendance->id ?>">
				<td colspan="<?= 2 + count($stat_types) ?>"><?php
				echo $this->Html->link(__('Add a sub'),
					['controller' => 'Games', 'action' => 'add_sub', '?' => ['game' => $game->id, 'team' => $attendance->id]],
					['onclick' => "add_sub({$game->id}, {$attendance->id}, 'stats', 'add_row_{$attendance->id}'); return false;"]);
				?></td>
			</tr>
<?php
endif;
?>

			<tr id="sub_row">
				<td><?= __('Unlisted Subs') ?></td>
				<td class="attendance_details"></td>
<?php
foreach ($stat_types as $stat):
?>

				<td>
<?php
	$i = \App\lib\fake_id();
	$stat_record = collection($game->stats)->firstMatch(['team_id' => $attendance->id, 'person_id' => 0, 'stat_type_id' => $stat->id]);
	if (!empty($stat_record)) {
		if (!empty($stat_record->id)) {
			echo $this->Form->hidden("stats.$i.id", ['value' => $stat_record->id]);
		}
	} else {
		$stat_record = $stats_table->newEntity([
			'game_id' => $game->id,
			'team_id' => $attendance->id,
			'person_id' => 0,
			'stat_type_id' => $stat->id,
			'value' => null,
		]);
	}
	echo $this->Form->hidden("stats.$i.game_id", ['value' => $stat_record->game_id]);
	echo $this->Form->hidden("stats.$i.team_id", ['value' => $stat_record->team_id]);
	echo $this->Form->hidden("stats.$i.person_id", ['value' => $stat_record->person_id]);
	echo $this->Form->hidden("stats.$i.stat_type_id", ['value' => $stat_record->stat_type_id]);
	echo $this->Form->control("stats.$i.value", ['div' => false, 'label' => false, 'size' => 3, 'type' => 'number', 'class' => "stat_{$stat->id}", 'data-stat-id' => $stat->id, 'value' => $stat_record->value]);
?>

				</td>
<?php
endforeach;
?>

			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th><?= __('Total') ?></th>
				<th class="attendance_details"></th>
<?php
foreach ($stat_types as $stat) {
	echo $this->Html->tag('th', 0, ['class' => "stat_{$stat->id}", 'data-handler' => $stat->sum_function, 'data-formatter' => $stat->formatter_function]);
}
?>

			</tr>
		</tfoot>
	</table>
</div>
