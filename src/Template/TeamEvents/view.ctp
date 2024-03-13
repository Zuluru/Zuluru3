<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TeamEvent $team_event
 */


use App\Authorization\ContextResource;
use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Team Event'));
$this->Html->addCrumb(h($team_event->team->name));
$this->Html->addCrumb(h($team_event->name));
$this->Html->addCrumb(__('View'));

$display_gender = $this->Authorize->can('display_gender', new ContextResource($team_event->team, ['division' => $team_event->team->division])) && Configure::read('offerings.genders') !== 'Open';
?>

<div class="team_events view">
	<h2><?= h($team_event->team->name) . ': ' . h($team_event->name) ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('Team') ?></dt>
		<dd><?= $this->element('Teams/block', ['team' => $team_event->team, 'show_shirt' => false]) ?></dd>
		<dt><?= __('Event') ?></dt>
		<dd><?= $team_event->name ?></dd>
<?php
if (!empty($team_event->description)):
?>
		<dt><?= __('Description') ?></dt>
		<dd><?= $team_event->description ?></dd>
<?php
endif;
?>
<?php
if (Configure::read('feature.urls') && !empty($team_event->website)):
?>
		<dt><?= __('Website') ?></dt>
		<dd><?= $this->Html->link($team_event->website, $team_event->website) ?></dd>
<?php
endif;
?>
		<dt><?= __('Date') ?></dt>
		<dd><?= $this->Time->date($team_event->date) ?></dd>
		<dt><?= __('Start') ?></dt>
		<dd><?= $this->Time->time($team_event->start) ?></dd>
		<dt><?= __('End') ?></dt>
		<dd><?= $this->Time->time($team_event->end) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= h($team_event->location_name) ?></dd>
		<dt><?= __('Address') ?></dt>
		<dd><?php
			$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
			$link_address = strtr($address, ' ', '+');
			echo $this->Html->link($address, "https://maps.google.com/maps?q=$link_address");
		?></dd>
<?php
if ($display_gender):
?>
		<dt><?= __('Totals') ?></dt>
		<dd><?php
			// Build the totals
			$statuses = Configure::read('attendance');
			$alt = Configure::read('attendance_alt');
			$count = array_fill_keys(array_keys($statuses), [Configure::read('gender.woman') => 0, Configure::read('gender.man') => 0]);
			$column = Configure::read('gender.column');
			foreach ($attendance as $record) {
				$person = collection($team_event->team->people)->firstMatch(['id' => $record->person_id]);
				if (empty($person))
					continue;
				$status = $record->status;
				++$count[$status][$person->$column];
			}

			foreach ($statuses as $status => $description) {
				$counts = [];
				foreach ([Configure::read('gender.woman'), Configure::read('gender.man')] as $gender) {
					if ($count[$status][$gender]) {
						$counts[] = $count[$status][$gender] . substr(__x('gender', $gender), 0, 1);
					}
				}
				if (!empty($counts)) {
					$low = strtolower($statuses[$status]);
					$short = $this->Html->iconImg("attendance_{$low}_dedicated_24.png", [
						'title' => __('Attendance: {0}', __($statuses[$status])),
						'alt' => $alt[$status],
					]);
					echo $short . ': ' . implode(' / ', $counts) . '&nbsp;';
				}
			}
		?></dd>
<?php
endif;
?>
	</dl>
</div>

<?php
if ($this->Authorize->can('edit', $team_event)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'event' => $team_event->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit Event')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'event' => $team_event->id],
	['alt' => __('Delete'), 'title' => __('Delete Event')],
	['confirm' => __('Are you sure you want to delete this team_event?')]));
echo $this->Html->tag('li', $this->Html->iconLink('team_event_add_32.png',
	['action' => 'add', 'team' => $team_event->team_id],
	['alt' => __('Add'), 'title' => __('Add Event')]));
?>
	</ul>
</div>
<?php
endif;
?>

<div class="related row">
	<div class="column">
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Name') ?></th>
						<th><?= __('Role') ?></th>
<?php
if ($display_gender):
?>
						<th><?= Configure::read('gender.label') ?></th>
<?php
endif;
?>
						<th><?= __('Attendance') ?></th>
						<th><?= __('Updated') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($team_event->team->people as $person):
	$record = collection($attendance)->firstMatch(['person_id' => $person->id]);
	if (!empty($record)):
?>
					<tr>
						<td><?= $this->element('People/block', compact('person')) ?></td>
						<td><?= Configure::read("options.roster_role.{$person->_joinData->role}") ?></td>
<?php
if ($display_gender):
?>
						<td><?= __($person->$column) ?></td>
<?php
endif;
?>
						<td><?=
							$this->element('TeamEvents/attendance_change', [
								'team' => $team_event->team,
								'event_id' => $team_event->id,
								'event' => $team_event,
								'person_id' => $person->id,
								'role' => $person->_joinData->role,
								'attendance' => $record,
								'dedicated' => true,
							])
						?></td>
						<td><?php
							if ($record->created != $record->modified) {
								echo $this->Time->datetime($record->modified);
							}
						?></td>
					</tr>
<?php
	endif;
endforeach;
?>

				</tbody>
			</table>
		</div>
	</div>
</div>
<?= $this->element('Games/attendance_div');
