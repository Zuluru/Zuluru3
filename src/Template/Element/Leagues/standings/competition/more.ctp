<?php
/**
 * @type \App\Model\Entity\Division $division
 * @type \App\Model\Entity\League $league
 * @type int $team_id
 * @type string[] $classes
 */

$classes[] = 'center';
$cols = 2 + $league->hasSpirit();
?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', 'division' => $division->id, 'team' => $team_id, 'full' => 1]) ?></td>
</tr>
