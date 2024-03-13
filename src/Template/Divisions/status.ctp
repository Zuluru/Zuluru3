<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Status Report'));
?>

<div class="divisions status_report">
	<h2><?= __('Status Report') . ': ' . $division->full_league_name ?></h2>

<?php
if ($playoffs_included) {
	echo $this->Html->para('warning-message', __('Note that this report includes only regular season games.'));
}
?>
	<table class="list tablesorter">
		<thead>
			<tr>
				<th class="header" rowspan="2"><?= __('Team') ?></th>
				<th class="header" rowspan="2"><?= __('Home %') ?></th>
<?php
if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')):
?>
				<th class="header" rowspan="2"><?= __('Preference %') ?></th>
<?php
endif;
?>

				<th class="sorter-false" colspan="<?= count($regions_used) + 2 ?>"><?= __('Games Played') ?></th>
				<th class="header" rowspan="2"><?= __('Opponents') ?></th>
				<th class="sorter-false" rowspan="2"><?= __('Repeat Opponents') ?></th>
			</tr>

			<tr>
				<th class="header"><?= __('Total') ?></th>
				<th class="header"><?= __('Home') ?></th>
<?php
foreach (array_keys($regions_used) as $region_id):
?>
				<th class="header"><?= __($regions[$region_id]) ?></th>
<?php
endforeach;
?>

			</tr>
		</thead>
		<tbody>
<?php
$total = 0;
foreach ($division->teams as $team):
	$team_stats = $stats[$team->id];
?>
			<tr>
				<td><?= $this->element('Teams/block', ['team' => $team, 'show_shirt' => false, 'options' => ['max_length' => 16]]) ?></td>
				<td><?php
					if ($team_stats['games'] > 0) {
						$ratio = sprintf('%.1f', round($team_stats['home_games'] * 100 / $team_stats['games'], 1));
						// When checking for a home games deficit, we don't want to flag it if the deficit
						// can be made up in a single game, i.e. there's no way for it to be exactly 50%
						// and they're just one game on the wrong side of it. So, when the game total is
						// odd, we want to add one to both for the comparison. As it happens, adding one
						// to both when it's an even total can't possibly change whether we're under 50%,
						// so we'll just do it that way all the time.
						$check_ratio = ($team_stats['home_games'] + 1) / ($team_stats['games'] + 1);
						if ($check_ratio < 0.5) {
							echo $this->Html->tag('span', $ratio, ['class' => 'warning-message']);
						} else {
							echo $ratio;
						}
					}
				?></td>
<?php
	if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')):
?>
				<td><?php
					if ($team_stats['games'] > 0) {
						$ratio = sprintf('%.1f', round($team_stats['field_rank'] * 100 / $team_stats['games'], 1));
						if ($ratio < 50) {
							echo $this->Html->tag('span', $ratio, ['class' => 'warning-message']);
						} else {
							echo $ratio;
						}
						$total += $ratio;
					}
				?></td>
<?php
	endif;
?>

				<td><?= $team_stats['games'] ?></td>
				<td><?= $team_stats['home_games'] ?></td>
<?php
	foreach (array_keys($regions_used) as $region_id):
?>
				<td><?= array_key_exists($region_id, $team_stats['region_games']) ? $team_stats['region_games'][$region_id] : '' ?></td>
<?php
	endforeach;
?>
				<td><?= count($team_stats['opponents']) ?></td>
				<td><?php
					$repeats = [];
					foreach ($team_stats['opponents'] as $opponent_id => $count) {
						if ($count > 2) {
							$repeats[] = $this->Html->tag('span',
									$this->element('Teams/block', ['team' => $division->teams[$opponent_id], 'show_shirt' => false, 'options' => ['max_length' => 16]]),
									['class' => 'warning-message']);
						} else if ($count > 1) {
							$repeats[] = $this->element('Teams/block', ['team' => $division->teams[$opponent_id], 'show_shirt' => false, 'options' => ['max_length' => 16]]);
						}
					}
					echo implode(',<br />', $repeats);
				?></td>
			</tr>
<?php
endforeach;
?>
		</tbody>
<?php
if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')):
?>
		<tfoot>
			<tr>
				<td><?= __('Average:') ?></td>
				<td></td>
				<td><?= sprintf('%.1f', round($total / count($division->teams), 1)) ?></td>
				<td colspan="<?= count($regions_used) + 4 ?>"></td>
			</tr>
		</tfoot>
<?php
endif;
?>
	</table>
</div>

<?php
// Make the table sortable
$this->Html->script(['jquery.tablesorter.min.js'], ['block' => true]);
$this->Html->css(['jquery.tablesorter.css'], ['block' => true]);
$this->Html->scriptBlock("zjQuery('.tablesorter').tablesorter({ sortInitialOrder: 'desc' });", ['buffer' => true]);
