<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var int $team_id
 * @var string[] $classes
 */

$classes[] = 'center';
$cols = 2 + $league->hasSpirit();
?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', '?' => ['division' => $division->id, 'team' => $team_id, 'full' => 1]]) ?></td>
</tr>
