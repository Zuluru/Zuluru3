<?php
$classes[] = 'center';
$cols = 10 + $league->hasSpirit();

?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', 'division' => $division->id, 'team' => $team_id, 'full' => 1]) ?></td>
</tr>
