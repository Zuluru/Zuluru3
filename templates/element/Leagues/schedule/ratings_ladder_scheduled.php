<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="schedule">
	<h2>Ratings ladder games successfully scheduled.</h2>
	<table width="100%">
		<thead>
			<tr>
				<th>Team 1</th>
				<th>Team 2</th>
				<th>Seed Diff<br>(total <?= array_sum($seed_closeness) ?>)</th>
				<th>Played each other<br>X games ago...</th>
			</tr>
		</thead>
		<tbody>
<?php
$team_idx = 0;
for ($i = 0; $i < count($gbr_diff); $i++):
	$class = '';
	$played = $gbr_diff[$i];
	if ($played != 0) {
		$class = ' class="warning"';
		$played = $gbr_diff[$i];
	} else {
		$played = '&nbsp;';
	}
?>
			<tr>
				<td<?= $class ?>><?= $versus_teams[$team_idx++] ?></td>
				<td<?= $class ?>><?= $versus_teams[$team_idx++] ?></td>
				<td<?= $class ?>><?= $seed_closeness[$i] ?></td>
				<td<?= $class ?>><?= $played ?></td>
			</tr>
<?php
endfor;
?>

		</tbody>
	</table>
</div>
