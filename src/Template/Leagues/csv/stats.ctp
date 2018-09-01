<?php
use Cake\Core\Configure;

$fp = fopen('php://output','w+');
$header1 = [
	'',
];

$header2 = [
	__('Name'),
];

foreach ($team->game as $key => $game) {
	$game_name = 'Game ' . ($key + 1) . ': ' . $this->Time->datetime($game->game_slot->start_time);
	foreach ($team->division->league->stat_types as $stat_type) {
		if (in_array($stat_type->type, Configure::read('stat_types.game'))) {
			$header1[] = $game_name;
			$game_name = '';
			$header2[] = $stat_type->name;
		}
	}
}

$season = __('Season');
foreach ($team->division->league->stat_types as $stat_type) {
	if (in_array($stat_type->type, Configure::read('stat_types.team'))) {
		$header1[] = $season;
		$season = '';
		$header2[] = $stat_type->name;
	}
}

fputcsv($fp, $header1);
fputcsv($fp, $header2);

foreach ($team->people as $person) {
	$data = [
		$person->full_name,
	];
	$person_stats = collection($team->stats)->match(['person_id' => $person->id]);

	foreach ($team->game as $key => $game) {
		$game_stats = $person_stats->match(['game_id' => $game->id]);
		foreach ($team->division->league->stat_types as $stat_type) {
			if (in_array($stat_type->type, Configure::read('stat_types.game'))) {
				if ($game_stats->isEmpty()) {
					$data[] = '';
				} else {
					$value = $game_stats->firstMatch(['stat_type_id' => $stat_type->id]);
					if (!empty($value)) {
						$data[] = $value->value;
					} else {
						$data[] = 0;
					}
				}
			}
		}
	}

	foreach ($team->division->league->stat_types as $stat_type) {
		if (in_array($stat_type->type, Configure::read('stat_types.team'))) {
			if (!empty($team->calculated_stats[$person->id][$stat_type->id])) {
				$data[] = $team->calculated_stats[$person->id][$stat_type->id];
			} else {
				if ($stat_type->type == 'season_calc') {
					$data[] = __('N/A');
				} else {
					$data[] = 0;
				}
			}
		}
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
