<?php
use Cake\Core\Configure;

if (!empty($teams) || $past_teams > 0):
?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th colspan="2"><?= $name . ' ' . $this->Html->help(['action' => 'teams', 'my_teams']) ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach ($teams as $team):
?>
			<tr>
				<td class="splash_item"><?php
					echo $this->element('Teams/block', ['team' => $team]) .
						' (' . $this->element('Divisions/block', ['division' => $team->division, 'field' => 'league_name']) . ')' .
						' (' . $this->element('People/roster_role', ['roster' => $team->_matchingData['TeamsPeople'], 'team' => $team, 'division' => $team->division]) . ')';
					if (!empty($team->division_id)) {
						$positions = Configure::read("sports.{$team->division->league->sport}.positions");
						if (!empty($positions)) {
							echo ' (' . $this->element('People/roster_position', ['roster' => $team->_matchingData['TeamsPeople'], 'team' => $team, 'division' => $team->division]) . ')';
						}
					}
				?></td>
				<td class="actions splash-action"><?php
					$is_captain = in_array($team->id, $this->UserCache->read('OwnedTeamIDs'));
					echo $this->element('Teams/actions', ['team' => $team, 'division' => $team->division, 'league' => $team->division->league, 'is_captain' => $is_captain, 'format' => 'links']);
				?></td>
			</tr>
<?php
	endforeach;
?>

		</tbody>
	</table>
</div>
<?php
	if ($past_teams > 0):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Show Team History'), ['controller' => 'People', 'action' => 'teams', 'person' => $id]));
?>
	</ul>
</div>
<div class="clear-float"></div>
<?php
	endif;
endif;
