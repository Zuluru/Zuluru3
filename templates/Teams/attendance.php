<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Game[] $games
 * @var \App\Model\Entity\Game[] $attendance
 * @var \App\Model\Entity\TeamEvent[] $event_attendance
 * @var int[] $days
 * @var \Cake\I18n\FrozenDate[] $dates
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Utility\Text;
use App\Model\Entity\Game;
use App\Model\Entity\GameSlot;
use App\Model\Table\GamesTable;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add(__('Season Attendance'));
$this->Breadcrumbs->add($team->name);
?>

<div class="teams attendance">
	<h2><?= __('Season Attendance') ?></h2>
<?php
$all_items = $event_attendance;

if (count($days) > 1) {
	$prefix = __('Week of') . ' ';
} else {
	$prefix = null;
}

foreach ($dates as $date) {
	$games_on_date = [];
	$match_dates = GamesTable::matchDates($date, $days);
	foreach ($match_dates as $match_date) {
		$games_on_date = array_merge($games_on_date, collection($games)->filter(function (Game $game) use ($match_date) {
			return $game->game_slot->game_date == $match_date;
		})->toArray());
	}
	if (!empty($games_on_date)) {
		foreach ($games_on_date as $game) {
			// Don't add cancelled or rescheduled games, or games already in the list
			if (!in_array($game->status, ['cancelled', 'rescheduled']) &&
				collection($all_items)->match(['id' => $game->id])->count() === 0
			) {
				$all_items[] = $game;
			}
		}
	} else {
		$all_items[] = new Game([
			'id' => null,
			'game_slot' => new GameSlot([
				'game_date' => $date,
				'game_start' => new FrozenTime('00:00:00'),
			]),
		]);
	}
}

usort($all_items, [GamesTable::class, 'compareDateAndField']);

$header_cells = [''];
foreach ($all_items as $item) {
	if (is_a($item, \App\Model\Entity\Game::class)) {
		if ($item->id) {
			$header_cells[] = $this->element('Games/block', ['game' => $item, 'game_slot' => $item->game_slot]);
		} else {
			$header_cells[] = $prefix . $this->Time->date($item->game_slot->game_date);
		}
	} else {
		$header_cells[] = $this->Html->link(__('{0} ({1})', $item->name, $this->Time->format($item->start_time, 'MMM d')),
			['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $item->id]],
			['title' => $this->Time->datetime($item->start_time)]
		);
	}
}
$header_cells[] = __('Total');
$header_cells[] = '';
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<?= $this->Html->tableHeaders($header_cells) ?>
			</thead>
			<tbody>
<?php
$statuses = Configure::read('attendance');
$count = array_fill_keys(array_keys($statuses), array_fill_keys(array_keys($all_items), [Configure::read('gender.woman') => 0, Configure::read('gender.man') => 0]));
$column = Configure::read('gender.column');
$people = $attendance->people ?? [];
foreach ($people as $person):
?>
				<tr>
					<td><?= $this->element('People/block', compact('person')) ?></td>
<?php
	$total = 0;
	foreach ($all_items as $key => $item):
		if (is_a($item, \App\Model\Entity\Game::class)) {
			$record = $item->getAttendance($person->attendances, collection($team->division->days)->extract('id')->toArray());
			if (empty($record)) {
				$out = __('N/A');
				$status = ATTENDANCE_UNKNOWN;
			} else {
				$status = $record->status;
				if ($status == ATTENDANCE_ATTENDING) {
					++$total;
				}
				++$count[$status][$key][$person->$column];
				$out = $this->element('Games/attendance_change', [
					'team' => $team,
					'game' => $item,
					'person_id' => $person->id,
					'role' => $person->_joinData->role,
					'attendance' => $record,
					'dedicated' => true,
				]);
			}
		} else {
			$record = collection($item->attendances)->firstMatch(['person_id' => $person->id]);
			if (empty($record)) {
				$out = __('N/A');
				$status = ATTENDANCE_UNKNOWN;
			} else {
				$status = $record->status;
				++$count[$status][$key][$person->$column];
				$out = $this->element('TeamEvents/attendance_change', [
					'team' => $team,
					'event_id' => $item->id,
					'event' => $item,
					'person_id' => $person->id,
					'role' => $person->_joinData->role,
					'attendance' => $record,
					'dedicated' => true,
				]);
			}
		}
?>
					<td><?= $out ?></td>
<?php
	endforeach;
?>
					<td><?= $total ?></td>
					<td><?= $this->element('People/block', compact('person')) ?></td>
				</tr>

<?php
endforeach;
?>

				<?= $this->Html->tableHeaders($header_cells) ?>
<?php
if ($this->Authorize->can('display_gender', new ContextResource($team, ['division' => $team->division]))):
	foreach ($statuses as $status => $description):
		$counts = [];
		foreach (array_keys($all_items) as $key) {
			foreach ([Configure::read('gender.woman'), Configure::read('gender.man')] as $gender) {
				if ($count[$status][$key][$gender]) {
					$counts[$key][] = $count[$status][$key][$gender] . substr(__x('gender', $gender), 0, 1);
				}
			}
		}
		if (!empty($counts)):
			$low = Text::slug(strtolower($description), '_');
			$icon = $this->Html->iconImg("attendance_{$low}_dedicated_24.png");
?>
				<tr>
					<td><?= $icon . '&nbsp;' . __($description) ?></td>
<?php
			foreach (array_keys($all_items) as $key):
?>
					<td><?php
						if (array_key_exists($key, $counts)) {
							if (Configure::read('offerings.genders') === 'Open') {
								echo array_sum($counts[$key]);
							} else {
								echo implode(' / ', $counts[$key]);
							}
						}
					?></td>
<?php
			endforeach;
?>
					<td></td>
					<td></td>
				</tr>
<?php
		endif;
	endforeach;
endif;
?>

			</tbody>
		</table>
	</div>
</div>

<div class="actions columns">
<?php
	if ($team->division) {
		echo $this->element('Teams/actions', ['team' => $team, 'division' => $team->division, 'league' => $team->division->league, 'format' => 'list']);
	} else {
		echo $this->element('Teams/actions', ['team' => $team, 'format' => 'list']);
	}
?>
</div>
<?= $this->element('Games/attendance_div');
