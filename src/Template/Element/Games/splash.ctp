<?php

use App\Authorization\ContextResource;
use Cake\Core\Configure;

if (!empty($items)):
?>
<div class="schedule table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th colspan="3"><?= __('Recent and Upcoming Schedule') . ' ' . $this->Html->help(['action' => 'games', 'recent_and_upcoming']) ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach ($items as $item):
?>
			<tr>
<?php
		if (is_a($item, 'App\Model\Entity\Game')):
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
					}
					if ($item->division->schedule_type != 'competition') {
						echo __(' vs. ');
						if ($item->away_team_id === null) {
							echo $item->away_dependency;
						} else {
							echo $this->element('Teams/block', ['team' => $item->away_team, 'options' => ['max_length' => 16]]) .
								__(' ({0})', __('away'));
						}
					}
					echo ' ' . __('at') . ' ';
					echo $this->element('Fields/block', ['field' => $item->game_slot->field]);
				?></td>
				<td class="actions splash-action"><?php
					if ($item->home_team && $item->away_team && in_array($item->home_team->id, $team_ids) && in_array($item->away_team->id, $team_ids)) {
						// This person is on both teams; pick the one they're more important on...
						// TODO: Better handling of this, as well as deal with game notes in such cases
						$home_role = collection($teams)->firstMatch(['id' => $item->home_team_id])->_matchingData['TeamsPeople']->role;
						$away_role = collection($teams)->firstMatch(['id' => $item->away_team_id])->_matchingData['TeamsPeople']->role;
						$importance = array_flip(array_reverse(array_keys(Configure::read('options.roster_role'))));
						if ($importance[$home_role] >= $importance[$away_role]) {
							$team = $item->home_team;
						} else {
							$team = $item->away_team;
						}
					} else if (in_array($item->home_team->id, $team_ids)) {
						$team = $item->home_team;
					} else {
						$team = $item->away_team;
					}
					if ($team->track_attendance) {
						$roster = collection($teams)->firstMatch(['id' => $team->id])->_matchingData['TeamsPeople'];
						if ($roster->status == ROSTER_APPROVED) {
							if (!empty($item->attendances)) {
								$record = collection($item->attendances)->firstMatch(['person_id' => $id]);
								echo $this->element('Games/attendance_change', [
									'team' => $team,
									'game' => $item,
									'person_id' => $id,
									'role' => $roster->role,
									'attendance' => $record,
									'future_only' => true,
								]);
							}
							if (!$item->game_slot->game_date->isPast()) {
								echo $this->Html->iconLink('attendance_24.png',
									['controller' => 'Games', 'action' => 'attendance', 'team' => $team->id, 'game' => $item->id],
									['alt' => __('Attendance'), 'title' => __('View Game Attendance Report')]);

								if ($this->Authorize->can('stat_sheet', new ContextResource($team, ['league' => $item->division->league, 'stat_types' => $item->division->league->stat_types]))) {
									echo $this->Html->iconLink('pdf_24.png',
										['controller' => 'Games', 'action' => 'stat_sheet', 'team' => $team->id, 'game' => $item->id],
										['alt' => __('Stat Sheet'), 'title' => __('Stat Sheet')],
										['confirm' => __('This stat sheet will only include players who have indicated that they are playing, plus a couple of blank lines.\n\nFor a stat sheet with your full roster, use the link from the team view page.')]);
								}
							}
						}
					}

					echo $this->Game->displayScore($item, $item->division, $item->division->league);

					if (Configure::read('feature.annotations')) {
						echo $this->Html->link(__('Add Note'), ['controller' => 'Games', 'action' => 'note', 'game' => $item->id]);
					}
				?></td>
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
				<td class="actions splash-action"><?php
					if ($item->team->track_attendance) {
						$roster = collection($teams)->firstMatch(['id' => $item->team->id])->_matchingData['TeamsPeople'];
						if (!empty($item->attendances)) {
							$record = collection($item->attendances)->firstMatch(['person_id' => $id]);
							if (!empty($roster) && $roster->status == ROSTER_APPROVED) {
								echo $this->element('TeamEvents/attendance_change', [
									'team' => $item->team,
									'event_id' => $item->id,
									'event' => $item,
									'person_id' => $id,
									'role' => $roster->role,
									'attendance' => $record,
								]);
							}
						}

						if (!$item->date->isPast()) {
							echo $this->Html->iconLink('attendance_24.png',
								['controller' => 'TeamEvents', 'action' => 'view', 'event' => $item->id],
								['alt' => __('Attendance'), 'title' => __('View Event Attendance')]);
						}
					}
				?></td>
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
				<td class="actions splash-action"><?php
					echo $this->Html->link(
						__('iCal'),
						['controller' => 'Task_slots', 'action' => 'ical', $item->id, 'task.ics']);
				?></td>
<?php
		// Generate blank space for placeholder splash screen
		elseif (is_null($item)):
?>
				<td class="splash_item">&nbsp;</td>
				<td class="splash_item">&nbsp;</td>
				<td class="splash_item">&nbsp;</td>
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
<?php
endif;
