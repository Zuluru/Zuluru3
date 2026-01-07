<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var string $uid_prefix
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;
use function App\Lib\ical_encode;

// Get domain URL for signing games
$domain = Configure::read('App.domain');

$field = "{$game->game_slot->field->long_name} ({$game->game_slot->field->facility->code})";
$field_address = ical_encode("{$game->game_slot->field->facility->location_street}, {$game->game_slot->field->facility->location_city}, {$game->game_slot->field->facility->location_province}");

// generate field url
$field_url = Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true);

// output game
$description = __('Officiating {0} at {1} on {2}',
	$game->division->long_league_name,
	ical_encode($field),
	$this->Time->iCalDateTimeRange($game->game_slot)
);
?>
BEGIN:VEVENT
UID:<?= "{$uid_prefix}{$game->id}@$domain" ?>

DTSTAMP:<?= $this->Time->iCal(FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($game->created) ?>

LAST-MODIFIED:<?= $this->Time->iCal($game->modified) ?>

DTSTART:<?= $this->Time->iCal($game->game_slot->start_time) ?>

DTEND:<?= $this->Time->iCal($game->game_slot->end_time) ?>

LOCATION;ALTREP=<?= "\"$field_url\":$field_address" ?>

GEO:<?= $game->game_slot->field->latitude ?>;<?= $game->game_slot->field->longitude ?>

X-LOCATION-URL:<?= $field_url ?>

SUMMARY:<?= $description ?>

DESCRIPTION:<?= $description ?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
