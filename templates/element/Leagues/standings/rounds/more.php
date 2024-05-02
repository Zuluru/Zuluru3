<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var int $team_id
 * @var string[] $classes
 */

$classes[] = 'center';
$cols = 11 + $league->hasSpirit() + $league->hasCarbonFlip() * 4;
if ($division->current_round != 1) {
	$cols += 8;
}
?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', '?' => ['division' => $division->id, 'team' => $team_id, 'full' => 1]]) ?></td>
</tr>
