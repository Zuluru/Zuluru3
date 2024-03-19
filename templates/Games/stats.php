<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var int $team_id
 */

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Game') . ' ' . $game->id);
if (isset($team_id)) {
	$this->Breadcrumbs->add($team->name);
}
$this->Breadcrumbs->add(__('Stats'));
?>

<div class="games stats">
	<h2><?= __('Game Stats') ?></h2>

	<dl class="dl-horizontal">
		<dt><?= __('League') . '/' . __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt><?= __('Home Team') ?></dt>
		<dd><?php
			echo $this->element('Teams/block', ['team' => $game->home_team]);
			if ($game->has('home_dependency')) {
				echo " ({$game->home_dependency})";
			}
		?></dd>
		<dt><?= __('Away Team') ?></dt>
		<dd><?php
			echo $this->element('Teams/block', ['team' => $game->away_team]);
			if ($game->has('away_dependency')) {
				echo " ({$game->away_dependency})";
			}
		?></dd>
<?php
if ($game->isFinalized()):
?>
		<dt><?= __('Score') ?></dt>
		<dd><?= $this->Game->displayScore($game, $game->division, $game->division->league) ?></dd>
<?php
endif;
?>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
	</dl>
</div>

<div class="related">
<?php
$na = __('N/A');

if (isset($team_id)) {
	$teams = [$team];
} else {
	$teams = [$team, $opponent];
}
foreach ($teams as $team):
	if (!isset($team_id)) {
		$header = $this->Html->tag('h3', $team->name);
	} else {
		$header = '';
	}

	// Sort the stats into groups for display
	$tables = [];
	foreach ($game->division->league->stat_types as $stat_type) {
		if (!array_key_exists($stat_type->positions, $tables)) {
			$tables[$stat_type->positions] = [
				'headers' => [
					$this->Html->tag('th', __('Name')),
				],
				'rows' => [],
				'totals' => [__('Total')],
			];
		}

		$tables[$stat_type->positions]['headers'][] = $this->Html->tag('th',
			$this->Html->tag('span', __($stat_type->abbr), ['title' => $stat_type->name]),
			['class' => $stat_type->class]
		);
		$total = [];

		foreach ($team->people as $person) {
			// TODO: Cache these collections instead of recreating them every time through the stat_type loop
			$person_stats = collection($game->stats)->match(['person_id' => $person->id, 'team_id' => $team->id]);
			if ($person_stats->isEmpty()) {
				continue;
			}

			if (!array_key_exists($person->id, $tables[$stat_type->positions]['rows'])) {
				$tables[$stat_type->positions]['rows'][$person->id] = [
					$this->element('People/block', compact('person')),
				];
			}
			$value = $person_stats->firstMatch(['stat_type_id' => $stat_type->id]);
			if (!empty($value)) {
				$value = $value->value;
				$total[] = $value;
			} else {
				$value = 0;
			}
			if (!empty($stat_type->formatter_function)) {
				$value = $sport_obj->{$stat_type->formatter_function}($value);
			}
			$tables[$stat_type->positions]['rows'][$person->id][] = [$value, ['class' => $stat_type->class]];
		}

		// Add the "unlisted subs" row.
		$person_stats = collection($game->stats)->match(['person_id' => 0, 'team_id' => $team->id]);
		if (!$person_stats->isEmpty()) {
			if (!array_key_exists(0, $tables[$stat_type->positions]['rows'])) {
				$tables[$stat_type->positions]['rows'][0] = [
					__('Subs'),
				];
			}
			$value = $person_stats->firstMatch(['stat_type_id' => $stat_type->id]);
			if (!empty($value)) {
				$value = $value->value;
				$total[] = $value;
			} else {
				$value = 0;
			}
			$tables[$stat_type->positions]['rows'][0][] = [$value, ['class' => $stat_type->class]];
		}

		if (empty($stat_type->sum_function)) {
			$total = array_sum($total);
		} else {
			$total = $sport_obj->{$stat_type->sum_function}($total);
		}
		if (!empty($stat_type->formatter_function)) {
			$total = $sport_obj->{$stat_type->formatter_function}($total);
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
		echo $header;
		$header = '';
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
endforeach;
?>

</div>

<?php
// Make the table sortable
$this->Html->script(['jquery.tablesorter.min.js'], ['block' => true]);
$this->Html->css('jquery.tablesorter.css', ['block' => true]);
$this->Html->scriptBlock("zjQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});", ['buffer' => true]);
