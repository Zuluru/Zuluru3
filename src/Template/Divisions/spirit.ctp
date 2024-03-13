<?php

use App\Model\Table\SpiritEntriesTable;
use Cake\Core\Configure;

/**
 * @var \App\Model\Entity\Division $division
 * @var \App\Module\Spirit $spirit_obj
 */

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Spirit Report'));
?>

<div class="divisions spirit">
<h3><?= __('Spirit Report') . ': ' . $division->full_league_name ?></h3>

<?php
echo $this->element('Spirit/legend', compact('spirit_obj'));

$rows = $team_records = $questions = [];
if ($division->league->numeric_sotg) {
	$questions[] = 'entered_sotg';
}
if ($division->league->sotg_questions != 'none') {
	$questions[] = 'assigned_sotg';
}
foreach ($spirit_obj->questions as $question => $detail) {
	if (!in_array($detail['type'], ['text', 'textarea'])) {
		$questions[] = $question;
	}
}
if (Configure::read('scoring.missing_score_spirit_penalty')) {
	$questions[] = 'score_entry_penalty';
}

$team_ids = collection($division->teams)->extract('id')->toArray();
$min_women = 99;
$max_women = 0;
if (!empty($team_ids)) {
	$team_records = [];
	foreach ($division->games as $game) {
		foreach (['home_team' => 'away_team', 'away_team' => 'home_team'] as $team => $opp) {
			if ($game->isFinalized()) {
				$id = $game->$team->id;
				if (!in_array($id, $team_ids)) {
					continue;
				}
				if (!array_key_exists($id, $team_records)) {
					$team_records[$id] = [
						'details' => $game->$team,
						'summary' => array_fill_keys($questions, null),
						'gender' => [],
						'games' => 0,
					];
				}

				$spirit_entry = $game->getSpiritEntry($game->$opp->id, $spirit_obj, true, true);
				if ($spirit_entry) {
					$spirit_entry->assigned_sotg = $spirit_obj->calculate($spirit_entry);
					++ $team_records[$id]['games'];
					foreach ($questions as $question) {
						$team_records[$id]['summary'][$question] += $spirit_entry[$question];
					}
				}

				if ($division->women_present) {
					$score_entry = $game->getScoreEntry($id);
					if ($score_entry && !empty($score_entry->women_present)) {
						if (!array_key_exists($score_entry->women_present, $team_records[$id]['gender'])) {
							$team_records[$id]['gender'][$score_entry->women_present] = 0;
						}
						++ $team_records[$id]['gender'][$score_entry->women_present];
						$min_women = min($min_women, $score_entry->women_present);
						$max_women = max($max_women, $score_entry->women_present);
					}
				}
			}
		}
	}
	$team_count = count($team_records);

	foreach ($team_records as $id => $team) {
		if ($team['games'] > 0) {
			if ($division->league->numeric_sotg) {
				$team_records[$id]['summary']['entered_sotg'] /= $team['games'];
			}
			if ($division->league->sotg_questions !== 'none') {
				$team_records[$id]['summary']['assigned_sotg'] /= $team['games'];
			}
			if (Configure::read('scoring.missing_score_spirit_penalty')) {
				$team_records[$id]['summary']['score_entry_penalty'] /= $team['games'];
			}
		}
	}

	usort($team_records, [SpiritEntriesTable::class, 'compareSpirit']);
?>

<h3><?= __('Team Spirit Summary') ?></h3>

<?php
	$header = [__('Team')];
	if ($division->league->numeric_sotg) {
		$header[] = __('Average Spirit');
	}
	if ($division->league->sotg_questions !== 'none') {
		$header[] = __('Assigned Spirit');
	}
	foreach ($spirit_obj->questions as $question => $detail) {
		if (!in_array($detail['type'], ['text', 'textarea'])) {
			$header[] = $detail['name'];
		}
	}
	if (Configure::read('scoring.missing_score_spirit_penalty')) {
		$header[] = __('Score Submitted?');
	}

	$rows = $overall = [];
	foreach ($team_records as $team) {
		$row = [$this->element('Teams/block', ['team' => $team['details'], 'show_shirt' => false])];
		if ($division->league->numeric_sotg) {
			$row[] = $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $division->league,
				'show_spirit_scores' => true,	// only ones allowed to even run this report
				'value' => $team['summary']['entered_sotg'],
			]);
			$overall['entered_sotg'][] = $team['summary']['entered_sotg'];
		}
		if ($division->league->sotg_questions != 'none') {
			$row[] = $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $division->league,
				'show_spirit_scores' => true,
				'value' => $team['summary']['assigned_sotg'],
			]);
			$overall['assigned_sotg'][] = $team['summary']['assigned_sotg'];
		}

		// This is to avoid divide-by-zero errors. No harm, since the numerators
		// will all be 0 as well if they didn't have any games...
		if ($team['games'] == 0) {
			$team['games'] = 1;
		}

		foreach ($spirit_obj->questions as $question => $detail) {
			if (!in_array($detail['type'], ['text', 'textarea'])) {
				$row[] = $this->element('Spirit/symbol', [
					'spirit_obj' => $spirit_obj,
					'league' => $division->league,
					'question' => $question,
					'show_spirit_scores' => true,	// only ones allowed to even run this report
					'value' => $team['summary'][$question] / $team['games'],
				]);
				$overall[$question][] = $team['summary'][$question] / $team['games'];
			}
		}

		if (Configure::read('scoring.missing_score_spirit_penalty')) {
			$row[] = $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $division->league,
				'question' => 'score_entry_penalty',
				'show_spirit_scores' => true,
				'value' => $team['summary']['score_entry_penalty'],
			]);
			$overall['score_entry_penalty'][] = $team['summary']['score_entry_penalty'];
		}

		$rows[] = $row;
	}

	$average = [[__('Division average'), ['class' => 'summary']]];
	$stddev = [[__('Division std dev'), ['class' => 'summary']]];
	foreach ($overall as $question => $col) {
		$average[] = [$this->element('Spirit/symbol', [
			'spirit_obj' => $spirit_obj,
			'league' => $division->league,
			'question' => $question,
			'show_spirit_scores' => true,	// only ones allowed to even run this report
			'value' => array_sum($col) / $team_count,
		]), ['class' => 'summary']];
		if (count($col) > 1) {
			$stddev[] = [sprintf('%0.2f', stats_standard_deviation($col)), ['class' => 'summary']];
		} else {
			$stddev[] = __('N/A');
		}
	}
	$rows[] = $average;
	$rows[] = $stddev;

	echo $this->Html->tag('div',
		$this->Html->tag('table', $this->Html->tableHeaders($header) . $this->Html->tableCells($rows), ['class' => 'table table-striped table-hover table-condensed']),
		['class' => 'table-responsive']
	);

	if ($division->league->numeric_sotg) {
		$bins = array_count_values(array_map('intval', $overall['entered_sotg']));
	} else {
		$bins = array_count_values(array_map('intval', $overall['assigned_sotg']));
	}
?>

<h2><?= __('Distribution of team average spirit scores') ?></h2>

<?php
	$header = [__('Spirit score'), __('Number of Teams'), __('Percentage of Division')];

	$max = $spirit_obj->max();
	if (array_key_exists($max, $bins)) {
		$rows = [[$max, $bins[$max], floor($bins[$max] / $team_count * 100)]];
	} else {
		$rows = [[$max, '', 0]];
	}
	for ($i = $max-1; $i >= 0; --$i) {
		if (array_key_exists($i, $bins)) {
			$rows[] = [$i . '-' . ($i + 1), $bins[$i], floor($bins[$i] / $team_count * 100)];
		} else {
			$rows[] = [$i . '-' . ($i + 1), '', 0];
		}
	}

	echo $this->Html->tag('div',
		$this->Html->tag('table', $this->Html->tableHeaders($header) . $this->Html->tableCells($rows), ['class' => 'table table-striped table-hover table-condensed']),
		['class' => 'table-responsive']
	);
}
?>

<h2><?= __('Spirit reports per game') ?></h2>

<?php
$header = [
	__('Game'),
	__('Entry By'),
	__('Given To'),
];
if ($division->league->numeric_sotg) {
	$header[] = __('Entered');
}
if ($division->league->sotg_questions != 'none') {
	$header[] = __('Assigned');
}

foreach ($spirit_obj->questions as $detail) {
	if (!in_array($detail['type'], ['text', 'textarea'])) {
		$header[] = $detail['name'];
	}
}

if (Configure::read('scoring.missing_score_spirit_penalty')) {
	$header[] = __('Score Submitted?');
}
if (Configure::read('scoring.most_spirited') && $division->most_spirited != 'never') {
	$header[] = __('Most Spirited');
}

$colcount = count($header);
$date = null;

$rows = [];
foreach ($division->games as $game) {
	if ($date != $game->game_slot->game_date) {
		$date = $game->game_slot->game_date;
		$date_row = [
			[$this->Html->tag('h3', $this->Time->date($game->game_slot->game_date)), ['colspan' => $colcount]],
		];
	}

	foreach (['home_team' => 'away_team', 'away_team' => 'home_team'] as $team => $opp) {
		foreach ($game->spirit_entries as $entry) {
			if ($date_row) {
				$rows[] = $date_row;
				$date_row = null;
			}

			if ($entry->created_team_id == $game->$team->id) {
				$row = [
					$this->Html->link($game->id, ['controller' => 'Games', 'action' => 'view', 'game' => $game->id]),
					$this->element('Teams/block', ['team' => $game->$team, 'show_shirt' => false]),
					$this->element('Teams/block', ['team' => $game->$opp, 'show_shirt' => false]),
				];
				if ($division->league->numeric_sotg) {
					$row[] = $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $division->league,
						'show_spirit_scores' => true,	// only ones allowed to even run this report
						'value' => $entry->entered_sotg,
					]);
				}
				if ($division->league->sotg_questions != 'none') {
					$row[] = $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $division->league,
						'show_spirit_scores' => true,	// only ones allowed to even run this report
						'value' => $spirit_obj->calculate($entry),
					]);
				}
				foreach ($spirit_obj->questions as $question => $detail) {
					if (!in_array($detail['type'], ['text', 'textarea'])) {
						$row[] = $this->element('Spirit/symbol', [
							'spirit_obj' => $spirit_obj,
							'league' => $division->league,
							'question' => $question,
							'show_spirit_scores' => true,	// only ones allowed to even run this report
							'entry' => $entry,
						]);
					}
				}
				if (Configure::read('scoring.missing_score_spirit_penalty')) {
					$row[] = $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $division->league,
						'question' => 'score_entry_penalty',
						'show_spirit_scores' => true,	// only ones allowed to even run this report
						'value' => $entry->score_entry_penalty,
					]);
				}
				if (Configure::read('scoring.most_spirited') && $division->most_spirited != 'never') {
					if (!empty($entry->most_spirited)) {
						$row[] = $this->element('People/block', ['person' => $entry->most_spirited]);
					} else {
						$row[] = '';
					}
				}
				$rows[] = $row;
				if (!empty($entry->comments)) {
					$rows[] = [
						[__('Comment for entry above:'), ['colspan' => 2]],
						[$entry->comments, ['class' => 'spirit-comments', 'colspan' => $colcount - 2]],
					];
				}
				if (!empty($entry->highlights)) {
					$rows[] = [
						[__('Highlight for entry above:'), ['colspan' => 2]],
						[$entry->highlights, ['class' => 'spirit-highlights', 'colspan' => $colcount - 2]],
					];
				}
			}
		}
		foreach ($game->incidents as $incident) {
			if ($incident->team_id == $game->$team->id) {
				$rows[] = [
					__('Incident for entry above:'),
					$incident->type,
					[$incident->details, ['class' => 'spirit-incident', 'colspan' => $colcount - 2]],
				];
			}
		}
	}
}

echo $this->Html->tag('div',
	$this->Html->tag('table', $this->Html->tableHeaders($header) . $this->Html->tableCells($rows), ['class' => 'table table-striped table-hover table-condensed']),
	['class' => 'table-responsive']
);

if ($division->women_present && $min_women <= $max_women):
	$team_records = collection($team_records)->sortBy('details.name', SORT_ASC, SORT_STRING | SORT_FLAG_CASE);
?>

<h3><?= __('Team Designated Women Summary') ?></h3>

<?php
	$header = range($min_women, $max_women);
	$overall = array_fill_keys($header, []);
	array_unshift($header, __('Team'));
	$rows = [];
	foreach ($team_records as $team) {
		$row = [$this->element('Teams/block', ['team' => $team['details'], 'show_shirt' => false])];
		for ($i = $min_women; $i <= $max_women; ++ $i) {
			if (array_key_exists($i, $team['gender'])) {
				$row[] = $overall[$i][] = $team['gender'][$i];
			} else {
				$row[] = $overall[$i][] = 0;
			}
		}
		$rows[] = $row;
	}

	$average = [[__('Division average'), ['class' => 'summary']]];
	$stddev = [[__('Division std dev'), ['class' => 'summary']]];
	foreach ($overall as $col) {
		$average[] = [sprintf('%0.2f', array_sum($col) / $team_count), ['class' => 'summary']];
		if (count($col) > 1) {
			$stddev[] = [sprintf('%0.2f', stats_standard_deviation($col)), ['class' => 'summary']];
		} else {
			$stddev[] = __('N/A');
		}
	}
	$rows[] = $average;
	$rows[] = $stddev;

	echo $this->Html->tag('div',
		$this->Html->tag('table', $this->Html->tableHeaders($header) . $this->Html->tableCells($rows), ['class' => 'table table-striped table-hover table-condensed']),
		['class' => 'table-responsive']
	);
endif;
?>

</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Download'), array_merge($this->getRequest()->getQueryParams(), ['_ext' => 'csv'])));
?>
	</ul>
</div>
