<div class="related">
<?php
if (!empty($teams)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
	echo $this->element("Leagues/view/{$league_obj->render_element}/heading", compact('division', 'league'));
	$seed = 0;
	foreach ($teams as $team) {
		$is_captain = in_array($team->id, $this->UserCache->read('AllOwnedTeamIDs'));
		$classes = [];
		if (floor($seed++ / 8) % 2 == 1) {
			$classes[] = 'tier-highlight';
		}
		$team->consolidateRoster($league->sport);
		echo $this->element("Leagues/view/{$league_obj->render_element}/team",
			compact('is_captain', 'team', 'division', 'league', 'seed', 'classes'));
	}
?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>
</div>
