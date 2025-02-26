<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Team $team
 * @var \App\Module\Spirit $spirit_obj
 * @var string[] $classes
 */

$class = null;
if (count($classes)) {
	$class = ' class="' . implode(' ', $classes). '"';
}
$season_record = \App\Model\Results\RoundRobinRecord::record($team, ['results' => 'season', 'default' => false]);
?>
<tr<?= $class?>>
	<td><?= $seed ?></td>
	<td><?= $this->element('Teams/block', ['team' => $team]) ?></td>
	<td><?= ($season_record ? $season_record->wins : '-') ?></td>
	<td><?= ($season_record ? $season_record->losses : '-') ?></td>
	<td><?= ($season_record ? $season_record->ties : '-') ?></td>
	<td><?= ($season_record ? $season_record->defaults : '-') ?></td>
	<td><?= ($season_record ? $season_record->goals_for : '-') ?></td>
	<td><?= ($season_record ? $season_record->goals_against : '-') ?></td>
	<td><?= ($season_record ? $season_record->goals_for - $season_record->goals_against : '-') ?></td>
	<td><?php
		if ($season_record && $season_record->streak > 1) {
			echo $season_record->streak . $season_record->streak_type;
		} else {
			echo '-';
		}
	?></td>
<?php
if ($league->hasSpirit()):
?>
	<td><?php
		if (!$season_record || $season_record->spirit_games == 0) {
			$spirit = null;
		} else {
			$spirit = $season_record->spirit / $season_record->spirit_games;
		}
		echo $this->element('Spirit/symbol', [
			'spirit_obj' => $spirit_obj,
			'league' => $league,
			'show_spirit_scores' => $show_spirit_scores,
			'value' => $spirit,
		]);
	?></td>
<?php
endif;
?>
</tr>
