<?php
use Cake\Core\Configure;

$class = null;
if (count($classes)) {
	$class = ' class="' . implode(' ', $classes). '"';
}
?>
<tr>
	<td><?= $this->element('Teams/block', ['team' => $team]) ?></td>
<?php
if (Configure::read('Perm.is_logged_in')):
?>
	<td><?php
		$roster_required = Configure::read("sports.{$league->sport}.roster_requirements.{$division->ratio_rule}");
		$count = $team->roster_count;
		if ((Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator) && $team->roster_count < $roster_required && $division->roster_deadline !== null) {
			echo $this->Html->tag('span', $count, ['class' => 'warning-message']);
		} else {
			echo $count;
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
	<td class="actions"><?= $this->element('Teams/actions', compact('team', 'division', 'league', 'is_captain')) ?></td>
</tr>
