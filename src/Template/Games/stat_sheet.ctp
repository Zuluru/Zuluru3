<?php
use Cake\Core\Configure;

$style = 'width:' . floor(80 / count($game->division->league->stat_types)) . '%;';
?>

<div class="stat-sheet">
	<h2><?= __('Stat Entry Sheet') ?></h2>
<?php // Seems that dompdf doesn't deal well with DLs that use floats ?>
	<table>
		<tr>
			<td><?= __('Date &amp; time') ?>:</td>
			<td><?= $this->Time->dateTimeRange($game->game_slot) ?></td>
		</tr>
		<tr>
			<td><?= __('Team') ?>:</td>
			<td><?php
				echo $team->name . ' (';
				if ($team->id == $game->home_team_id) {
					echo __('home');
				} else {
					echo __('away');
				}
				echo ')';
			?></td>
		</tr>
		<tr>
			<td><?= __('Opponent') ?>:</td>
			<td><?php
				echo $opponent->name . ' (';
				if ($opponent->id == $game->home_team_id) {
					echo __('home');
				} else {
					echo __('away');
				}
				echo ')';
			?></td>
		</tr>
		<tr>
			<td><?= __('Location') ?>:</td>
			<td><?= $game->game_slot->field->long_name ?></td>
		</tr>
		<tr>
			<td><?= __('Final score') ?>:</td>
			<td><?= $team->name ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?= __('Opponent') ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td><?= __('Timeouts taken') ?>:</td>
			<td><?= $team->name ?>: [&nbsp;] [&nbsp;] [&nbsp;]  <?= __('Opponent') ?>: [&nbsp;] [&nbsp;] [&nbsp;]</td>
		</tr>
<?php
if (Configure::read("sports.{$game->division->league->sport}.start.stat_sheet")):
?>
		<tr>
			<td><?= __(Configure::read("sports.{$game->division->league->sport}.start.stat_sheet")) ?>:</td>
			<td><?= $team->name ?>: [&nbsp;]  <?= __('Opponent') ?>: [&nbsp;]<?=
				Configure::read("sports.{$game->division->league->sport}.start.stat_sheet_direction") ? __('End') : '' ?></td>
		</tr>
<?php
elseif (Configure::read("sports.{$game->division->league->sport}.start.stat_sheet_direction")):
?>
		<tr>
			<td><?= __('Starting end') ?>:</td>
			<td><?= $team->name ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?= __('Opponent') ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>
<?php
endif;
?>
	</table>

	<table>
		<thead>
			<tr>
				<th><?= __('Player') ?></th>
<?php
foreach ($game->division->league->stat_types as $stat) {
	echo $this->Html->tag('th', __($stat->name), compact('style'));
}
?>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($attendance->people as $person):
	if (!empty($person->attendances) && $person->attendances[0]->status == ATTENDANCE_ATTENDING):
?>
			<tr>
				<td><?= $person->full_name ?></td>
<?php
		foreach ($game->division->league->stat_types as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
		endforeach;
?>
			</tr>
<?php
	endif;
endforeach;
?>

			<tr>
				<td></td>
<?php
foreach ($game->division->league->stat_types as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
endforeach;
?>
			</tr>
			<tr>
				<td></td>
<?php
foreach ($game->division->league->stat_types as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
endforeach;
?>
			</tr>
			<tr>
				<td></td>
<?php
foreach ($game->division->league->stat_types as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
endforeach;
?>
			</tr>
			<tr>
				<td><?= __('Unlisted Subs') ?></td>
<?php
foreach ($game->division->league->stat_types as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
endforeach;
?>
			</tr>
		</tbody>
	</table>

	<fieldset>
		<legend><?= __('Game Notes') ?></legend>
		<p><br /><br /><br /><br /><br /><br /><br /><br /></p>
	</fieldset>
</div>
