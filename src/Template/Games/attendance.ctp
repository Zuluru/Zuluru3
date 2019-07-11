<?php
use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Utility\Text;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Attendance'));
$this->Html->addCrumb($team->name);
$this->Html->addCrumb($this->Time->date($game->game_slot->game_date));

$display_gender = $this->Authorize->can('display_gender', new ContextResource($team, ['division' => $game->division]));
?>

<div class="games attendance">
<h2><?= __('Attendance') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('League') . '/' . __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Html->link($this->Time->dateTimeRange($game->game_slot), ['action' => 'view', 'game' => $game->id]) ?></dd>
		<dt><?= __('Team') ?></dt>
		<dd><?= $this->element('Teams/block', ['team' => $team]) ?></dd>
		<dt><?= __('Opponent') ?></dt>
		<dd><?= $this->element('Teams/block', ['team' => $opponent]) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
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
		foreach ($attendance->people as $person) {
			if (!array_key_exists(0, $person->attendances)) {
				continue;
			}
			$record = $person->attendances[0];
			$status = $record->status;
			++$count[$status][$person->$column];
		}

		foreach ($statuses as $status => $description) {
			$counts = [];
			foreach ([Configure::read('gender.woman'), Configure::read('gender.man')] as $gender) {
				if ($count[$status][$gender]) {
					// TODOFUO: Better option than the substr method, that's going to break
					$counts[] = $count[$status][$gender] . substr(__x('gender', $gender), 0, 1);
				}
			}
			if (!empty($counts)) {
				$low = Text::slug(strtolower($statuses[$status]), '_');
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

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if ($this->Authorize->can('note', new ContextResource($game, ['home_team' => $game->home_team, 'away_team' => $game->away_team]))) {
	echo $this->Html->tag('li', $this->Html->link(__('Add Note'), ['action' => 'note', 'game' => $game->id]));
}
if ($this->Authorize->can('stat_sheet', new ContextResource($team, ['league' => $game->division->league, 'stat_types' => $game->division->league->stat_types]))) {
	echo $this->Html->tag('li', $this->Html->iconLink('pdf_32.png',
		['controller' => 'Games', 'action' => 'stat_sheet', 'team' => $team->id, 'game' => $game->id],
		['alt' => __('Stat Sheet'), 'title' => __('Stat Sheet')],
		['confirm' => __('This stat sheet will only include players who have indicated that they are playing, plus a couple of blank lines.\n\nFor a stat sheet with your full roster, use the link from the team view page.')]));
}
?>
	</ul>
</div>

<div class="related">
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
foreach ($attendance->people as $person):
	if (!array_key_exists(0, $person->attendances))
		continue;
	$record = $person->attendances[0];
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
					<td><?php
					echo $this->element('Games/attendance_change', [
						'team' => $team,
						'game' => $game,
						'person_id' => $person->id,
						'role' => $person->_joinData->role,
						'attendance' => $record,
						'dedicated' => true,
					]);
					?></td>
					<td><?php
					if ($record->created != $record->modified) {
						echo $this->Time->datetime($record->modified);
					}
					?></td>
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
<?= $this->element('Games/attendance_div');
