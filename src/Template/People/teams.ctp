<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type $teams \App\Model\Entity\Team[]
 */

use App\Model\Entity\Team;
use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Team History'));
?>

<div class="teams index">
	<h2><?= __('Team History') . ': ' . $person->full_name ?></h2>

<?php
echo $this->Selector->selector('Year', $this->Selector->extractOptions(
	$teams,
	function (Team $item) { return $item->division ? $item->division->open : null; },
	'year'
));
echo $this->Selector->selector('Season', $this->Selector->extractOptionsUnsorted(
	$teams,
	function (Team $item) { return $item->division ? $item->division->league : null; },
	'season'
));
echo $this->Selector->selector('Day', $this->Selector->extractOptions(
	$teams,
	function (Team $item) { return $item->division && !empty($item->division->days) ? $item->division->days : null; },
	'name', 'id'
));
echo $this->Selector->selector('Role', $this->Selector->extractOptions(
	$teams,
	function (Team $item) { return $item->_matchingData['TeamsPeople']; },
	function (\App\Model\Entity\TeamsPerson $roster) { return Configure::read("options.roster_role.{$roster->role}"); }
));
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
		$classes = collection($teams)->filter(function ($test) use ($year) {
			return !empty($test->division_id) && $test->division->open >= "$year-01-01" && $test->division->open <= "$year-12-31";
		})->extract(function (Team $team) {
			return "select_id_{$team->id}";
		})->toArray();

		$last_year = $year;
?>
			<tr class="<?= implode(' ', $classes) ?>">
				<th colspan="3"><?= $year_text ?></th>
			</tr>
<?php
	endif;
?>
			<tr class="select_id_<?= $team->id ?>">
				<td><?= $this->element('Teams/block', compact('team')) ?></td>
				<td><?= Configure::read("options.roster_role.{$team->_matchingData['TeamsPeople']->role}") ?></td>
				<td><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></td>
<?php
endforeach;
?>
			</tr>
		</table>
	</div>
</div>
