<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Module\Spirit $spirit_obj
 */

use Cake\Core\Configure;

$fp = fopen('php://output','w+');
$header = [
	__('Team'),
	__('TeamID'),
	__('Submitted By'),
	__('Team Score'),
	__('Opp Score'),
];
if ($division->league->numeric_sotg) {
	$header[] = __('Spirit');
}
if ($division->league->sotg_questions != 'none') {
	$header[] = __('Calc Spirit');
}
if (Configure::read('scoring.missing_score_spirit_penalty')) {
	$header[] = __('Score Entry Penalty');
}
foreach ($spirit_obj->questions as $question => $detail) {
	$header[] = $detail['name'];
}
if (Configure::read('scoring.most_spirited') && $division->most_spirited != 'never') {
	$header[] = __('Most Spirited');
}
fputcsv($fp, $header);

$teams = collection($division->teams)->extract('id')->toArray();
$results = [];
foreach ($division->games as $game) {
	foreach (['home_team' => 'away_team', 'away_team' => 'home_team'] as $team => $opp) {
		if (!$game->isFinalized()) {
			continue;
		}

		foreach ($game->spirit_entries as $spirit_entry) {
			if (!in_array($spirit_entry->team_id, $teams)) {
				continue;
			}

			$game_results[] = [
				$game->$team->name,
				$game->$team->id,
				$spirit_entry->created_team_id ? $game->$opp->name : __('Official'),
				($team == 'home_team' ? $game->home_score : $game->away_score),
				($team == 'home_team' ? $game->away_score : $game->home_score),
			];

			if ($division->league->numeric_sotg) {
				$game_results[] = $spirit_entry->entered_sotg;
			}
			if ($division->league->sotg_questions != 'none') {
				$game_results[] = $spirit_obj->calculate($spirit_entry);
			}
			if (Configure::read('scoring.missing_score_spirit_penalty')) {
				$game_results[] = $spirit_entry->score_entry_penalty;
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				$game_results[] = $spirit_entry->$question;
			}
			if (Configure::read('scoring.most_spirited') && $division->most_spirited != 'never') {
				if (!empty($spirit_entry->most_spirited)) {
					$game_results[] = $spirit_entry->most_spirited->full_name;
				} else {
					$game_results[] = '';
				}
			}

			$results[] = $game_results;
		}
	}
}

foreach($results as $row) {
	fputcsv($fp, $row);
}

fclose($fp);
