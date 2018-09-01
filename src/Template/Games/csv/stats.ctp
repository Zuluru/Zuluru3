<?php
$fp = fopen('php://output','w+');
if (isset($team_id)) {
	$header = [];
	$teams = [$team];
} else {
	$header = [__('Team')];
	$teams = [$team, $opponent];
}
$header[] = __('Name');

foreach ($game->division->league->stat_types as $stat_type) {
	$header[] = $stat_type->name;
}
fputcsv($fp, $header);

foreach ($teams as $team) {
	foreach ($team->people as $person) {
		$data = [];
		if (!isset($team_id)) {
			$data[] = $team->name;
		}

		$person_stats = collection($game->stats)->match(['person_id' => $person->id, 'team_id' => $team->id]);

		$data[] = $person->full_name;

		foreach ($game->division->league->stat_types as $stat_type) {
			$value = $person_stats->firstMatch(['stat_type_id' => $stat_type->id]);
			if ($value) {
				$data[] = $value->value;
			} else {
				$data[] = 0;
			}
		}

		// Output the data row
		fputcsv($fp, $data);
	}

	$data = [];
	if (!isset($team_id)) {
		$data[] = $team->name;
	}

	$person_stats = collection($game->stats)->match(['person_id' => 0, 'team_id' => $team->id]);

	$data[] = __('Subs');

	foreach ($game->division->league->stat_types as $stat_type) {
		$value = $person_stats->firstMatch(['stat_type_id' => $stat_type->id]);
		if ($value) {
			$data[] = $value->value;
		} else {
			$data[] = 0;
		}
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
