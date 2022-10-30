<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
 * @type $team_id int
 * @type $classes string[]
 */

$classes[] = 'center';
$cols = 10 + $league->hasSpirit();

?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', 'division' => $division->id, 'team' => $team_id, 'full' => 1]) ?></td>
</tr>
