<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Teams'));
$this->Html->addCrumb($team->name);
$this->Html->addCrumb(__('Stats'));
?>

<div class="teams stats">
<h2><?= $team->name ?></h2>
</div>
<div class="actions columns">
	<?= $this->element('Teams/actions', ['team' => $team, 'division' => $team->division, 'league' => $team->division->league, 'format' => 'list']) ?>
</div>

<div class="related">
<?php
$na = __('N/A');

$has_numbers = Configure::read('feature.shirt_numbers') && $team->has('people') && collection($team->people)->some(function ($person) {
	return $person->_joinData->number != null;
});

$headers = [
	$this->Html->tag('th', __('Name')),
];
$totals = [__('Total')];
if ($has_numbers) {
	array_unshift($headers, $this->Html->tag('th', '#'));
	array_unshift($totals, '');
}

// Sort the stats into groups for display
$tables = [];
foreach ($team->division->league->stat_types as $stat_type) {
	if (!array_key_exists($stat_type->positions, $tables)) {
		$tables[$stat_type->positions] = [
			'headers' => $headers,
			'rows' => [],
			'totals' => $totals,
		];
	}

	$tables[$stat_type->positions]['headers'][] = $this->Html->tag('th',
		$this->Html->tag('span', __($stat_type->abbr), ['title' => $stat_type->name]),
		['class' => $stat_type->class]
	);
	$total = [];

	foreach ($team->people as $person) {
		if (!array_key_exists($person->id, $tables[$stat_type->positions]['rows'])) {
			$tables[$stat_type->positions]['rows'][$person->id] = [
				$this->element('People/block', compact('person')),
			];
			if ($has_numbers) {
				array_unshift($tables[$stat_type->positions]['rows'][$person->id], $person->_joinData->number);
			}
		}
		if (array_key_exists($person->id, $team->calculated_stats) &&
			array_key_exists($stat_type->id, $team->calculated_stats[$person->id]))
		{
			$value = $team->calculated_stats[$person->id][$stat_type->id];
			if ($stat_type->type == 'season_total') {
				$total[] = $team->calculated_stats[$person->id][$stat_type->id];
			}
		} else {
			if ($stat_type->type == 'season_calc') {
				$value = $na;
			} else {
				$value = 0;
			}
		}
		if (!empty($stat_type->formatter_function)) {
			$value = $sport_obj->{$stat_type->formatter_function}($value);
		}
		$tables[$stat_type->positions]['rows'][$person->id][] = [$value, ['class' => $stat_type->class]];
	}

	if ($stat_type->type == 'season_total') {
		if (empty($stat_type->sum_function)) {
			$total = array_sum($total);
		} else {
			$total = $sport_obj->{$stat_type->sum_function}($total);
		}
		if (!empty($stat_type->formatter_function)) {
			$total = $sport_obj->{$stat_type->formatter_function}($total);
		}
	} else {
		$total = '';
	}
	$tables[$stat_type->positions]['totals'][] = $total;
}

foreach ($tables as $positions => $table):
	// Maybe prune out rows that are all zeroes; don't do it for the main stats block for all positions
	if (!empty($positions)) {
		foreach ($table['rows'] as $key => $row) {
			$remove = true;

			// Skip name column
			array_shift($row);

			while (!empty($row)) {
				$value = array_shift($row);
				if ($value[0] != 0 && $value[0] != $na) {
					$remove = false;
					break;
				}
			}
			if ($remove) {
				unset($table['rows'][$key]);
			}
		}
	}

	if (empty($table['rows'])) {
		continue;
	}
?>
	<table class="list tablesorter">
		<thead>
		<tr>
			<?= implode('', $table['headers']) ?>
		</tr>
		</thead>
		<tbody>
			<?= $this->Html->tableCells(array_values($table['rows'])) ?>
		</tbody>
		<tfoot>
		<tr>
			<?= $this->Html->tableHeaders($table['totals']) ?>
		</tr>
		</tfoot>
	</table>
<?php
endforeach;
?>

</div>

<?php
// Make the table sortable
$this->Html->script(['jquery.tablesorter.min.js'], ['block' => true]);
$this->Html->css('jquery.tablesorter.css', ['block' => true]);
$this->Html->scriptBlock("zjQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});", ['buffer' => true]);
