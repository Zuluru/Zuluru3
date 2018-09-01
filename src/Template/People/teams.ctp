<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Team History'));
?>

<div class="teams index">
	<h2><?= __('Team History') . ': ' . $person->full_name ?></h2>

<?php
$years = [];
foreach ($teams as $team) {
	if ($team->division->open->year != 0) {
		$years[] = $team->division->open->year;
		$seasons[] = $team->division->league->season;
	}
}
echo $this->element('selector', ['title' => 'Year', 'options' => array_unique($years)]);

$seasons = array_unique(collection($teams)->extract('division.league.season')->toArray());
echo $this->element('selector', ['title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)]);

$days = collection($teams)->extract('division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
echo $this->element('selector', ['title' => 'Day', 'options' => $days]);

$roles = array_unique(collection($teams)->extract('_matchingData.TeamsPeople.role')->toArray());
echo $this->element('selector', ['title' => 'Role', 'options' => array_intersect(array_keys(Configure::read('options.roster_role')), $roles)]);
?>
	<div class="table-responsive clear-float">
		<table class="table table-striped table-hover table-condensed">
<?php
$last_year = null;
foreach ($teams as $team):
	if ($team->division->open->year != 0) {
		$year = $year_text = $team->division->open->year;
	} else {
		$year = '0000';
		$year_text = __('N/A');
	}
	if ($last_year != $year):
		$last_year = $year;
		$seasons = $days = $roles = [];
		foreach ($teams as $year_team) {
			if (!empty($year_team->division_id) && $year_team->division->open >= "$year-01-01" && $year_team->division->open <= "$year-12-31") {
				$seasons[] = $year_team->division->league->season;
				$days = array_merge($days, collection($year_team->division->days)->extract('name')->toArray());
				$roles[] = $year_team->_matchingData['TeamsPeople']->role;
			}
		}
		$seasons = array_unique($seasons);
		$days = array_unique($days);
		$roles = array_unique($roles);
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Year', 'options' => $year]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $days]) ?> <?= $this->element('selector_classes', ['title' => 'Role', 'options' => $roles]) ?>">
				<th colspan="3"><?= $year_text ?></th>
			</tr>
<?php
	endif;
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Year', 'options' => $year]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $team->division->league->season]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => array_unique(collection($team->division->days)->extract('name')->toArray())]) ?> <?= $this->element('selector_classes', ['title' => 'Role', 'options' => $team->_matchingData['TeamsPeople']->role]) ?>">
				<td><?= $this->element('Teams/block', compact('team')) ?></td>
				<td><?= $team->_matchingData['TeamsPeople']->role ?></td>
				<td><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></td>
<?php
endforeach;
?>
			</tr>
		</table>
	</div>
</div>
