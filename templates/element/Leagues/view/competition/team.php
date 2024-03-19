<?php
/**
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Team $team
 * @var string[] $classes
 * @var int $seed
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;

$class = null;
if (count($classes)) {
	$class = ' class="' . implode(' ', $classes). '"';
}
?>
<tr>
	<td><?= $this->element('Teams/block', ['team' => $team]) ?></td>
	<td><?= $team->rating ?></td>
<?php
if ($this->Authorize->can('view_roster', \App\Controller\TeamsController::class)):
?>
	<td><?php
		$roster_required = Configure::read("sports.{$league->sport}.roster_requirements.{$division->ratio_rule}");
		if ($this->Authorize->can('add_player', new ContextResource($team, ['division' => $division])) && $team->roster_count < $roster_required && !$division->roster_deadline_passed) {
			echo $this->Html->tag('span', $team->roster_count, ['class' => 'warning-message']);
		} else {
			echo $team->roster_count;
		}
	?></td>
<?php
	if (Configure::read('profile.skill_level')):
?>
	<td><?= $team->average_skill ?></td>
<?php
	endif;
endif;
?>
	<td class="actions"><?= $this->element('Teams/actions', compact('team', 'division', 'league')) ?></td>
</tr>
