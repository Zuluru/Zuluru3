<?php
use Cake\Core\Configure;

$style = 'width:' . floor(80 / count($team['division']['league']['stat_types'])) . '%;';
?>

<div class="stat-sheet">
	<h2><?= __('Stat Entry Sheet') ?></h2>
<?php // Seems that dompdf doesn't deal well with DLs that use floats ?>
	<table>
		<tbody>
			<tr>
				<td><?= __('Date &amp; time') ?>:</td>
				<td></td>
			</tr>
			<tr>
				<td><?= __('Team') ?>:</td>
				<td><?= $team['name'] . __(' ({0})', __('home/away')) ?></td>
			</tr>
			<tr>
				<td><?= __('Opponent') ?>:</td>
				<td></td>
			</tr>
			<tr>
				<td><?= __('Location') ?>:</td>
				<td></td>
			</tr>
			<tr>
				<td><?= __('Final score') ?>:</td>
				<td><?= $team['name'] ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?= __('Opponent') ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td><?= __('Timeouts taken') ?>:</td>
				<td><?= $team['name'] ?>: [&nbsp;] [&nbsp;] [&nbsp;]  <?= __('Opponent') ?>: [&nbsp;] [&nbsp;] [&nbsp;]</td>
			</tr>
<?php
if (Configure::read("sports.{$team->division->league->sport}.start.stat_sheet")):
?>
			<tr>
				<td><?= __(Configure::read("sports.{$team->division->league->sport}.start.stat_sheet")) ?>:</td>
				<td><?= $team['name'] ?>: [&nbsp;]  <?= __('Opponent') ?>: [&nbsp;]<?=
				Configure::read("sports.{$team->division->league->sport}.start.stat_sheet_direction") ? __('End') : '' ?></td>
			</tr>
<?php
elseif (Configure::read("sports.{$team->division->league->sport}.start.stat_sheet_direction")):
?>
			<tr>
				<td><?= __('Starting end') ?>:</td>
				<td><?= $team['name'] ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?= __('Opponent') ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
<?php
endif;
?>
		</tbody>
	</table>

	<table>
		<thead>
			<tr>
				<th><?= __('Player') ?></th>
<?php
foreach ($team['division']['league']['stat_types'] as $stat) {
	echo $this->Html->tag('th', __($stat['name']), compact('style'));
}
?>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($team['people'] as $person):
?>
			<tr>
				<td><?= $person['full_name'] ?></td>
<?php
	foreach ($team['division']['league']['stat_types'] as $stat):
?>
				<td style="<?= $style ?>">&nbsp;</td>
<?php
	endforeach;
?>

			</tr>
<?php
endforeach;
?>

			<tr>
				<td><?= __('Unlisted Subs') ?></td>
<?php
foreach ($team['division']['league']['stat_types'] as $stat):
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
