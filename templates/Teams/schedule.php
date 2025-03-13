<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\Routing\Router;
use App\Model\Table\GamesTable;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Schedule'));

$annotate = $this->Authorize->can('view_notes', $team);
$display_attendance = $this->Authorize->can('attendance', $team);

if (!empty($team->division->header)):
?>
<div class="division_header"><?= $team->division->header ?></div>
<?php
endif;

$context = new ContextResource($team->division, ['league' => $team->division->league]);
$show_spirit = $this->Authorize->can('view_spirit', $context);
$show_spirit_scores = $show_spirit && $this->Authorize->can('view_spirit_scores', $context);

$display_gender = $this->Authorize->can('display_gender', new ContextResource($team, ['division' => $team->division]));
?>
<div class="teams schedule">
<h2><?= __('Team Schedule') . ': ' . $team->name ?></h2>
<?php
if (!empty($team['games'])):
?>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<tr>
			<th><?= __('Date') ?></th>
			<th><?= __('Time') ?></th>
			<th><?= __(Configure::read("sports.{$team->division->league->sport}.field_cap")) ?></th>
			<th><?= $team->division->schedule_type == 'competition' ? '' : __('Opponent') ?></th>
			<th><?= __('Score') ?></th>
<?php
	if ($show_spirit):
?>
			<th><?= __('Spirit') ?></th>
<?php
	endif;

	if ($display_attendance):
?>
			<th><?= __('Attendance') ?></th>
<?php
	endif;

	if ($annotate):
?>
			<th><?= __('Notes') ?></th>
<?php
	endif;
?>
		</tr>
<?php
	foreach ($team->games as $game):
		$class = null;
		$is_event = is_a($game, \App\Model\Entity\TeamEvent::class);
		if (!$is_event) {
			if (!$game->published) {
				$class = ' class="unpublished"';
			}
			GamesTable::adjustEntryIndices($game);
			$game->readDependencies();
			if ($show_spirit && !in_array($game->status, Configure::read('unplayed_status')) &&
				$game->isFinalized() && array_key_exists($team->id, $game->spirit_entries))
			{
				$entry = $game->spirit_entries[$team->id];
			} else {
				$entry = null;
			}
		}
?>
		<tr<?= $class ?>>
			<td><?php
			if ($is_event) {
				echo $this->Time->fulldate($game->date);
			} else {
				echo $this->Time->fulldate($game->game_slot->start_time);
			}
			?></td>
			<td><?php
			if ($is_event) {
				echo $this->Html->link($this->Time->timeRange($game), ['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $game->id]]);
			} else {
				echo $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);
			}
			?></td>
			<td><?php
			if ($is_event) {
				$address = "{$game->location_street}, {$game->location_city}, {$game->location_province}";
				$link_address = strtr($address, ' ', '+');
				echo $this->Html->link($game->location_name, "https://maps.google.com/maps?q=$link_address");
			} else {
				echo $this->element('Fields/block', ['field' => $game->game_slot->field]);
			}
			?></td>
			<td><?php
			if ($is_event) {
				echo $this->Html->link($game->display_name, ['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $game->id]]);
			} else if ($team->division->schedule_type != 'competition') {
				if ($team->id == $game->home_team_id) {
					if ($game->away_team_id === null) {
						echo $game->away_dependency . __(' ({0})', __('away'));
					} else {
						echo $this->element('Teams/block', ['team' => $game->away_team]) . __(' ({0})', __('away'));
					}
				} else {
					if ($game->home_team_id === null) {
						echo $game->home_dependency . __(' ({0})', __('home'));
					} else {
						echo $this->element('Teams/block', ['team' => $game->home_team]) . __(' ({0})', __('home'));
					}
				}
			}
			?></td>
			<td class="actions"><?php
			if (!$is_event) {
				echo $this->Game->displayScore($game, $team->division, $team->division->league, $team->id);
			}
			?></td>
<?php
		if ($show_spirit):
?>
			<td><?php
			if (!$is_event) {
				echo $this->element('Spirit/symbol', [
					'spirit_obj' => $spirit_obj,
					'league' => $team->division->league,
					'show_spirit_scores' => $show_spirit_scores,
					'entry' => $entry,
				]);
			}
			?></td>
<?php
		endif;

		if ($display_attendance):
?>
			<td class="actions"><?php
			if ($is_event) {
				echo $this->Html->link(__('View'), ['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $game->id]]);
			} else {
				echo $this->Html->link(__('View'), ['controller' => 'Games', 'action' => 'attendance', '?' => ['team' => $team->id, 'game' => $game->id]]);
			}

			if ($display_gender) {
				$column = Configure::read('gender.column');
				$counts = collection($game->attendances)->countBy(function ($attendance) use ($column) {
					return $attendance->person->$column;
				})->toArray();
				if (Configure::read('offerings.genders') === 'Open') {
					echo array_sum($counts);
				} else {
					if ($team->division->ratio_rule != 'womens') {
						$counts += [Configure::read('gender.man') => 0];
					}
					if ($team->division->ratio_rule != 'mens') {
						$counts += [Configure::read('gender.woman') => 0];
					}
					krsort($counts);
					$counts = collection($counts)->map(function ($count, $gender) {
						return $count . substr(__x('gender', $gender), 0, 1);
					})->toArray();
					echo implode(' / ', $counts);
				}
			}
			?></td>
<?php
		endif;

		if ($annotate):
?>
			<td class="actions"><?php
			if (!$is_event) {
				echo $this->Html->link(__('Add'), ['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]]);
			}
			?></td>
<?php
		endif;
?>
		</tr>
<?php
	endforeach;
?>
	</table>
	</div>
<?php
	if ($team->division->league->hasSpirit()) {
		echo $this->element('Spirit/legend', compact('spirit_obj'));
	}

endif;
?>
<p><?= __('Home vs away designations shown are for the opponent, not the team whose schedule this is.') ?></p>
<?php
if (!empty($team->division_id) && $team->division->close > FrozenDate::now()->subDays(14)):
?>
<p><?= __('Get your team schedule in {0} format or {1}.',
	$this->Html->iconLink('ical.gif',
		['action' => 'ical', $team->id, 'team.ics'],
		['alt' => __('iCal')]
	),
	$this->Html->imageLink('gc_button6.gif',
		'https://www.google.com/calendar/render?cid=' . Router::url(['_scheme' => 'http', 'action' => 'ical', $team->id], true),
		['alt' => __('Add to Google Calendar')],
		['target' => 'google']
	)
) ?></p>
<?php
endif;
?>
</div>

<div class="actions columns">
	<?= $this->element('Teams/actions', ['team' => $team, 'division' => $team->division, 'league' => $team->division->league, 'format' => 'list']) ?>
</div>
<?php
if (!empty($team->division->footer)):
?>
<div class="clear-float division_footer"><?= $team->division->footer ?></div>
<?php
endif;
