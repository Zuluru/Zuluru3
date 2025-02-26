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
?>
<tr<?= $class?>>
	<td><?= $this->element('Teams/block', ['team' => $team]) ?></td>
	<td><?= $team->rating ?></td>
<?php
if ($league->hasSpirit()):
	$season_record = \App\Model\Results\RoundRobinRecord::record($team, ['results' => 'season', 'default' => false]);
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
