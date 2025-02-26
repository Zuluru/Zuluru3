<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Team'));
$this->Breadcrumbs->add(__('Spirit'));
$this->Breadcrumbs->add($team->name);
?>

<div class="teams spirit">
<h2><?= __('Team Spirit') . ': ' . $team->name ?></h2>

<?php
$header = [
	__('Game'),
	__('Entry By'),
];
if ($team->division->league->numeric_sotg) {
	$header[] = __('Entered');
}
if ($team->division->league->sotg_questions != 'none') {
	$header[] = __('Assigned');
}

// TODO: Move display details into an element to share between this, division spirit report, maybe others
foreach ($spirit_obj->questions as $detail) {
	if (!in_array($detail['type'], ['text', 'textarea'])) {
		$header[] = $detail['name'];
	}
}

if (Configure::read('scoring.missing_score_spirit_penalty')) {
	$header[] = __('Score Submitted?');
}

$rows = [];
foreach ($team->games as $game) {
	foreach ($game->spirit_entries as $entry) {
		if ($entry->team_id == $team->id) {
			if ($entry->created_team_id == $game->home_team->id) {
				$from = $game->home_team;
			} else {
				$from = $game->away_team;
			}
			$row = [
				$this->Html->link($this->Time->date($game->game_slot->game_date), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]),
				$this->element('Teams/block', ['team' => $from, 'show_shirt' => false]),
			];
			if ($team->division->league->numeric_sotg) {
				$row[] = $entry->entered_sotg;
			}
			if ($team->division->league->sotg_questions != 'none') {
				$row[] = $spirit_obj->calculate($entry);
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				if (!in_array($detail['type'], ['text', 'textarea'])) {
					$row[] = $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $team->division->league,
						'question' => $question,
						'show_spirit_scores' => true,	// only ones allowed to even run this report
						'entry' => $entry,
					]);
				}
			}
			if (Configure::read('scoring.missing_score_spirit_penalty')) {
				$row[] = $this->element('Spirit/symbol', [
					'spirit_obj' => $spirit_obj,
					'league' => $team->division->league,
					'question' => 'score_entry_penalty',
					'show_spirit_scores' => true,	// only ones allowed to even run this report
					'value' => $entry->score_entry_penalty,
				]);
			}
			$rows[] = $row;
			$colcount = count($row);
			if (!empty($entry->comments)) {
				$rows[] = [
					[__('Comment for entry above:'), ['colspan' => 2]],
					[$entry->comments, ['colspan' => $colcount - 2]],
				];
			}
			if (!empty($entry->highlights)) {
				$rows[] = [
					[__('Highlight for entry above:'), ['colspan' => 2]],
					[$entry->highlights, ['colspan' => $colcount - 2]],
				];
			}
		}
	}
}

echo $this->Html->tag('table', $this->Html->tableHeaders($header) . $this->Html->tableCells($rows), ['class' => 'list']);
?>

</div>
