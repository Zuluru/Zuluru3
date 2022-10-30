<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $show_teams \App\Model\Entity\Team[]
 * @type $league_obj \App\Module\LeagueType
 * @type $team_id int
 * @type $more_before boolean
 * @type $more_after boolean
 */

use App\Authorization\ContextResource;

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Standings'));
?>

<?php
if (!empty($division->header)):
?>
<div class="division_header"><?= $division->header ?></div>
<?php
endif;
?>
<div class="divisions standings">
	<h2><?= __('Division Standings') . ': ' . $division->full_league_name ?></h2>
<?php
$has_season = collection($division->teams)->some(function ($team) {
	return !empty($team->_results->season->games);
});
$has_tournament = (!empty($division->_results->pools) || !empty($division->_results->brackets));
$can_edit = $this->Authorize->can('edit_schedule', $division);

$context = new ContextResource($division, ['league' => $division->league]);
$show_spirit = $this->Authorize->can('view_spirit', $context);
$show_spirit_scores = $show_spirit && $this->Authorize->can('view_spirit_scores', $context);

if (!empty($division->teams) && ($has_season || !$has_tournament)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
<?php
	echo $this->element("Leagues/standings/{$league_obj->render_element}/heading", [
		'league' => $division->league,
		'division' => $division,
		'can_edit' => $can_edit,
	]);
?>
			</thead>
			<tbody>
<?php
	if ($more_before) {
		$seed = $more_before;
		echo $this->element("Leagues/standings/{$league_obj->render_element}/more", [
			'league' => $division->league,
			'division' => $division,
			'can_edit' => $can_edit,
			'team_id' => $team_id,
		]);
	} else {
		$seed = 0;
	}
	foreach ($show_teams as $team) {
		$classes = [];
		if (floor($seed++ / 8) % 2 == 1) {
			$classes[] = 'tier-highlight';
		}
		if ($team_id == $team->id) {
			$classes[] = 'team-highlight';
		}
		echo $this->element("Leagues/standings/{$league_obj->render_element}/team", [
			'league' => $division->league,
			'division' => $division,
			'can_edit' => $can_edit,
			'show_spirit_scores' => $show_spirit_scores,
			'team' => $team,
			'seed' => $seed,
			'classes' => $classes,
		]);
	}
	if ($more_after) {
		echo $this->element("Leagues/standings/{$league_obj->render_element}/more", [
			'league' => $division->league,
			'division' => $division,
			'can_edit' => $can_edit,
			'team_id' => $team_id,
		]);
	}
?>
			</tbody>
		</table>
	</div>
<?php
	if ($division->league->hasSpirit()) {
		echo $this->element('Spirit/legend', compact('spirit_obj'));
	}
endif;

if (!empty($division->_results->pools)):
	echo $this->element('Leagues/standings/tournament/notice');
?>
	<h3><?= __('Preliminary rounds') ?></h3>
<?php
	echo $this->element('Leagues/standings/tournament/pools', [
		'division' => $division,
		'can_edit' => $can_edit,
		'league' => $division->league,
		'games' => $division->_results->pools,
		'teams' => $division->teams,
	]);
endif;

if (!empty($division->_results->brackets)):
?>
	<h3><?= __('Playoff brackets') ?></h3>
<?php
	echo $this->element('Leagues/standings/tournament/bracket', [
		'division' => $division,
		'can_edit' => $can_edit,
		'league' => $division->league,
		'games' => $division->_results->brackets,
		'teams' => $division->teams,
	]);
endif;
?>
</div>
<div class="actions columns"><?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?></div>
<?php
if (!empty($division->footer)):
?>
<div class="division_footer"><?= $division->footer ?></div>
<?php
endif;
