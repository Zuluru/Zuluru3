<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

// Get domain URL for signing games
$domain = Configure::read('App.domain');

if (!is_array($team_id)) {
	$team_id = [$team_id];
}

if (in_array($game->home_team->id, $team_id)) {
	$my_team = $game->home_team;
	$my_home_away = __('home');
	$opponent = $game->away_team;
	$opp_home_away = __('away');
} else {
	$my_team = $game->away_team;
	$my_home_away = __('away');
	$opponent = $game->home_team;
	$opp_home_away = __('home');
}

$field = "{$game->game_slot->field->long_name} ({$game->game_slot->field->facility->code})";
$field_address = \App\Lib\ical_encode("{$game->game_slot->field->facility->location_street}, {$game->game_slot->field->facility->location_city}, {$game->game_slot->field->facility->location_province}");

// generate field url
$field_url = Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true);

// output game
?>
BEGIN:VEVENT
UID:<?= "$uid_prefix$game_id@$domain" ?>

DTSTAMP:<?= $this->Time->iCal(\Cake\I18n\FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($game->created) ?>

LAST-MODIFIED:<?= $this->Time->iCal($game->modified) ?>

DTSTART:<?= $this->Time->iCal($game->game_slot->start_time) ?>

DTEND:<?= $this->Time->iCal($game->game_slot->end_time) ?>

LOCATION;ALTREP=<?= "\"$field_url\":$field_address" ?>

GEO:<?= $game->game_slot->field->latitude ?>;<?= $game->game_slot->field->longitude ?>

X-LOCATION-URL:<?= $field_url ?>

SUMMARY:<?= __('{0} vs {1}', \App\Lib\ical_encode("{$my_team->name} ($my_home_away)"), \App\Lib\ical_encode("{$opponent->name} ($opp_home_away)")) ?>

DESCRIPTION:<?= __('{0} vs {1} at {2} on {3}',
	\App\Lib\ical_encode("{$my_team->name} ($my_home_away)"),
	\App\Lib\ical_encode("{$opponent->name} ($opp_home_away)"),
	\App\Lib\ical_encode($field),
	$this->Time->iCalDateTimeRange($game->game_slot)
);
if (Configure::read('feature.shirt_colour') && !empty($opponent->shirt_colour)):
	echo __(' ({0})', __('they wear {0}', \App\Lib\ical_encode($opponent->shirt_colour)));
?>

X-OPPONENT-COLOUR:<?= $opponent->shirt_colour ?>
<?php
endif;
?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
