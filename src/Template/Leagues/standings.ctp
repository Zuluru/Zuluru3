<?php
use App\Model\Entity\Division;

$tournaments = collection($league->divisions)->every(function (Division $division) {
	return $division->schedule_type == 'tournament';
});
$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
$this->Html->addCrumb($league->full_name);
$this->Html->addCrumb(__('Standings'));
?>

<div class="leagues standings">
	<h2><?= ($tournaments ? __('Tournament Standings') : __('League Standings')) . ': ' . $league->full_name ?></h2>
<?php
foreach ($league->divisions as $division):
	if (!empty($division->header) && !empty($division->games)):
?>
	<div class="division_header"><?= $division->header ?></div>
<?php
	endif;

	$has_season = collection($division->teams)->some(function ($team) {
		return !empty($team->_results->season->games);
	});
	$has_tournament = (!empty($division->_results->pools) || !empty($division->_results->brackets));
	if (!empty($division->teams) && ($has_season || !$has_tournament)):
		if (count($league->divisions) > 1 && !empty($division->name)):
?>
	<h3><?= $division->name ?></h3>
<?php
		endif;
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
<?php
		echo $this->element("Leagues/standings/{$division->render_element}/heading", [
			'is_coordinator' => $is_coordinator,
			'league' => $league,
			'division' => $division,
		]);
?>
			</thead>
			<tbody>
<?php
		$seed = 0;
		foreach ($division->teams as $team) {
			$classes = [];
			if (floor($seed++ / 8) % 2 == 1) {
				$classes[] = 'tier-highlight';
			}
			echo $this->element("Leagues/standings/{$division->render_element}/team", [
				'is_coordinator' => $is_coordinator,
				'league' => $league,
				'division' => $division,
				'team' => $team,
				'seed' => $seed,
				'classes' => $classes,
			]);
		}
		?>
			</tbody>
		</table>
	</div>
<?php
		if ($league->hasSpirit()) {
			echo $this->element('Spirit/legend', compact('spirit_obj'));
		}
	endif;

	if (!empty($division->_results->pools)):
		echo $this->element('Leagues/standings/tournament/notice');
?>
	<h4><?= __('Preliminary rounds') ?></h4>
<?php
		echo $this->element('Leagues/standings/tournament/pools', ['division' => $division, 'league' => $league, 'games' => $division->_results->pools, 'teams' => $division->teams]);
	endif;

	if (!empty($division->_results->brackets)):
?>
	<h4><?= __('Playoff brackets') ?></h4>
<?php
		echo $this->element('Leagues/standings/tournament/bracket', ['division' => $division, 'league' => $league, 'games' => $division->_results->brackets, 'teams' => $division->teams]);
	endif;

	if (!empty($division->footer) && !empty($division->games)):
?>
		<div class="division_footer"><?= $division->footer ?></div>
<?php
	endif;
endforeach;
?>
</div>

<div class="actions columns"><?= $this->element('Leagues/actions', [
	'league' => $league,
	'format' => 'list',
	'tournaments' => $tournaments,
]) ?></div>
