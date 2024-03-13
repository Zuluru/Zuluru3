<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

$fp = fopen('php://output','w+');
$header = [
	__('Name'),
];

foreach ($team->division->league->stat_types as $stat_type) {
	$header[] = $stat_type->name;
}
fputcsv($fp, $header);

foreach ($team->people as $person) {
	$data = [
		$person->full_name,
	];

	foreach ($team->division->league->stat_types as $stat_type) {
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

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
