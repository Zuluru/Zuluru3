<?php
/**
 * @var \Cake\ORM\Entity[] $items
 * @var \App\Model\Entity\Person[] $relatives
 * @var \App\Model\Entity\Team[] $teams
 */
?>
<div class="schedule table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th><?= $this->UserCache->read('Person.full_name') ?></th>
<?php
foreach ($relatives as $relative):
?>
				<th><?= $relative->full_name ?></th>
<?php
endforeach;
?>
				<th></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($items as $item):
?>
			<tr>
<?php
	if (is_a($item, 'App\Model\Entity\Game')):
		$home_attendance = $this->Authorize->can('attendance', new \App\Authorization\ContextResource($item, ['home_team' => $item->home_team]));
		$away_attendance = $this->Authorize->can('attendance', new \App\Authorization\ContextResource($item, ['away_team' => $item->away_team]));
?>
				<td class="splash_item"><?= $this->Html->link($this->Time->dateTimeRange($item->game_slot), ['controller' => 'Games', 'action' => 'view', 'game' => $item->id]) ?></td>
				<td class="splash_item"><?php
					$item->readDependencies();
					if ($item->home_team_id === null) {
						echo $item->home_dependency;
					} else {
						echo $this->element('Teams/block', ['team' => $item->home_team, 'options' => ['max_length' => 16]]);
						if ($item->division->schedule_type != 'competition') {
							echo __(' ({0})', __('home'));
						}
						if ($home_attendance && !$item->game_slot->game_date->isPast()) {
							echo $this->Html->iconLink('attendance_24.png',
								['controller' => 'Games', 'action' => 'attendance', 'team' => $item->home_team->id, 'game' => $item->id],
								['alt' => __('Attendance'), 'title' => __('View Game Attendance Report')]);
						}
					}
					if ($item->division->schedule_type != 'competition') {
						echo ' ' . __('vs.') . ' ';
						if ($item->away_team_id === null) {
							echo $item->away_dependency;
						} else {
							echo $this->element('Teams/block', ['team' => $item->away_team, 'options' => ['max_length' => 16]]) .
								__(' ({0})', __('away'));
							if ($away_attendance && !$item->game_slot->game_date->isPast()) {
								echo $this->Html->iconLink('attendance_24.png',
									['controller' => 'Games', 'action' => 'attendance', 'team' => $item->away_team->id, 'game' => $item->id],
									['alt' => __('Attendance'), 'title' => __('View Game Attendance Report')]);
							}
						}
					}
					echo ' ' . __('at') . ' ';
					echo $this->element('Fields/block', ['field' => $item->game_slot->field]);
				?></td>
				<td class="actions splash_item"><?php
					if ($home_attendance && !empty($item->attendances)) {
						$roster = collection($teams)->firstMatch(['id' => $item->home_team->id, '_matchingData.TeamsPeople.person_id' => $id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED &&
							$item->game_slot->game_date >= $roster->_matchingData['TeamsPeople']->created
						) {
							$record = collection($item->attendances)->firstMatch(['person_id' => $id]);
							echo $this->element('Games/attendance_change', [
								'team' => $item->home_team,
								'game' => $item,
								'person_id' => $id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
					if ($away_attendance && !empty($item->attendances)) {
						$roster = collection($teams)->firstMatch(['id' => $item->away_team->id, '_matchingData.TeamsPeople.person_id' => $id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED &&
							$item->game_slot->game_date >= $roster->_matchingData['TeamsPeople']->created
						) {
							$record = collection($item->attendances)->firstMatch(['person_id' => $id]);
							echo $this->element('Games/attendance_change', [
								'team' => $item->away_team,
								'game' => $item,
								'person_id' => $id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
				?></td>
<?php
		foreach ($relatives as $relative):
?>
				<td class="actions splash_item"><?php
					if ($home_attendance && !empty($item->attendances)) {
						$roster = collection($teams)->firstMatch(['id' => $item->home_team->id, '_matchingData.TeamsPeople.person_id' => $relative->id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED &&
							$item->game_slot->game_date >= $roster->_matchingData['TeamsPeople']->created
						) {
							$record = collection($item->attendances)->firstMatch(['person_id' => $relative->id]);
							echo $this->element('Games/attendance_change', [
								'team' => $item->home_team,
								'game' => $item,
								'person_id' => $relative->id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
					if ($away_attendance && !empty($item->attendances)) {
						$roster = collection($teams)->firstMatch(['id' => $item->away_team->id, '_matchingData.TeamsPeople.person_id' => $relative->id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED &&
							$item->game_slot->game_date >= $roster->_matchingData['TeamsPeople']->created
						) {
							$record = collection($item->attendances)->firstMatch(['person_id' => $relative->id]);
							echo $this->element('Games/attendance_change', [
								'team' => $item->away_team,
								'game' => $item,
								'person_id' => $relative->id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
				?></td>
<?php
		endforeach;
?>
				<td><?= $this->Game->displayScore($item, $item->division, $item->division->league) ?></td>
<?php
	elseif (is_a($item, 'App\Model\Entity\TeamEvent')):
?>
				<td class="splash_item"><?php
					$time = $this->Time->day($item->date) . ', ' .
							$this->Time->time($item->start) . '-' .
							$this->Time->time($item->end);
					echo $this->Html->link($time, ['controller' => 'TeamEvents', 'action' => 'view', 'event' => $item->id]);
				?></td>
				<td class="splash_item"><?php
					echo $this->element('Teams/block', ['team' => $item->team, 'show_shirt' => false]) . ' ' .
						__('event') . ': ';
					if (!empty($item->website)) {
						echo $this->Html->link($item->name, $item->website);
					} else {
						echo $item->name;
					}
					echo ' ' . __('at') . ' ';
					$address = "{$item->location_street}, {$item->location_city}, {$item->location_province}";
					$link_address = strtr($address, ' ', '+');
					echo $this->Html->link($item->location_name, "https://maps.google.com/maps?q=$link_address");
				?></td>
				<td class="actions splash_item"><?php
					if (!empty($item->attendances) && $item->team->track_attendance) {
						$roster = collection($teams)->firstMatch(['id' => $item->team->id, '_matchingData.TeamsPeople.person_id' => $id]);
						$record = collection($item->attendances)->firstMatch(['person_id' => $id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED && $record) {
							echo $this->element('TeamEvents/attendance_change', [
								'team' => $item->team,
								'event_id' => $item->id,
								'event' => $item,
								'person_id' => $id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
				?></td>
<?php
		foreach ($relatives as $relative):
?>
				<td class="actions splash_item"><?php
					if (!empty($item->attendances) && $item->team->track_attendance) {
						$roster = collection($teams)->firstMatch(['id' => $item->team->id, '_matchingData.TeamsPeople.person_id' => $relative->id]);
						$record = collection($item->attendances)->firstMatch(['person_id' => $relative->id]);
						if (!empty($roster) && $roster->_matchingData['TeamsPeople']->status == ROSTER_APPROVED && $record) {
							echo $this->element('TeamEvents/attendance_change', [
								'team' => $item->team,
								'event_id' => $item->id,
								'event' => $item,
								'person_id' => $relative->id,
								'role' => $roster->_matchingData['TeamsPeople']->role,
								'attendance' => $record,
								'dedicated' => true,
							]);
						}
					}
				?></td>
<?php
		endforeach;
?>
				<td></td>
<?php
	elseif (is_a($item, 'App\Model\Entity\TaskSlot')):
?>
				<td class="splash_item"><?php
					$time = $this->Time->day($item->task_date) . ', ' .
							$this->Time->time($item->task_start) . '-' .
							$this->Time->time($item->task_end);
					echo $this->Html->link($time, ['controller' => 'Tasks', 'action' => 'view', 'task' => $item->task->id]);
				?></td>
				<td class="splash_item"><?php
					echo $this->Html->link($item->task->translateField('name'), ['controller' => 'Tasks', 'action' => 'view', 'task' => $item->task->id]) .
						__(' ({0})', __('report to {0}', $this->element('People/block', ['person' => $item->task->person])));
				?></td>
				<td class="splash_item"><?php
					if ($item->person_id == $id) {
						echo $this->Html->iconImg('attendance_attending_dedicated_24.png');
					}
				?></td>
<?php
		foreach ($relatives as $relative):
?>
				<td class="splash_item"><?php
					if ($item->person_id == $relative->id) {
						echo $this->Html->iconImg('attendance_attending_dedicated_24.png');
					}
				?></td>
<?php
		endforeach;
?>
				<td class="actions"><?php
					echo $this->Html->link(
						__('iCal'),
						['controller' => 'Task_slots', 'action' => 'ical', $item->id, 'task.ics']);
				?></td>
<?php
	endif;
?>
			</tr>
<?php
endforeach;
?>

		</tbody>
	</table>
</div>
