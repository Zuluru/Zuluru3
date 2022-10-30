<?php
use Cake\Core\Configure;

/**
 * @type $division \App\Model\Entity\Division
 * @type $spirit_obj \App\Module\Spirit
 */

$fp = fopen('php://output','w+');
$header = [
	__('Team'),
	__('TeamID'),
	__('Opponent'),
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
$team_results = [];
foreach ($division->games as $game) {
	foreach (['home_team' => 'away_team', 'away_team' => 'home_team'] as $team => $opp) {
		if ($game->isFinalized()) {
			$id = $game->$team->id;
			if (!in_array($id, $teams)) {
				continue;
			}
			if (!array_key_exists($id, $team_results)) {
				$team_results[$id] = [
					$game->$team->name,
					$game->$team->id,
				];
			}
			$team_results[$id][] = $game->$opp->name;
			$team_results[$id][] = ($team == 'home_team' ? $game->home_score : $game->away_score);
			$team_results[$id][] = ($team == 'home_team' ? $game->away_score : $game->home_score);

			$spirit_entry = $game->getSpiritEntry($id, $spirit_obj, true, true);
			if ($spirit_entry) {
				$spirit_entry->assigned_sotg = $spirit_obj->calculate($spirit_entry);
			}

			if ($division->league->numeric_sotg) {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry->entered_sotg;
				} else {
					$team_results[$id][] = '';
				}
			}
			if ($division->league->sotg_questions != 'none') {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry->assigned_sotg;
				} else {
					$team_results[$id][] = '';
				}
			}
			if (Configure::read('scoring.missing_score_spirit_penalty')) {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry->score_entry_penalty;
				} else {
					$team_results[$id][] = '';
				}
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry->$question;
				} else {
					$team_results[$id][] = '';
				}
			}
			if (Configure::read('scoring.most_spirited') && $division->most_spirited != 'never') {
				if (!empty($spirit_entry->most_spirited)) {
					$team_results[$id][] = $spirit_entry->most_spirited->full_name;
				} else {
					$team_results[$id][] = '';
				}
			}
		}
	}
}

foreach($team_results as $row) {
	fputcsv($fp, $row);
}

fclose($fp);
