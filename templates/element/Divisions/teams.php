<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Team[] $teams
 * @var \App\Module\LeagueType $league_obj
 * @var bool $can_edit
 */

?>
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
		$classes = [];
		if (floor($seed++ / 8) % 2 == 1) {
			$classes[] = 'tier-highlight';
		}
		$team->consolidateRoster($league->sport);
		echo $this->element("Leagues/view/{$league_obj->render_element}/team",
			compact('team', 'division', 'league', 'seed', 'classes', 'can_edit'));
	}
?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>
</div>
