<?php
use Cake\Core\Configure;

// Get domain URL for signing events
$domain = Configure::read('App.domain');

$location_name = strtr($event->location_name, '()', '[]');
$location_address = \App\Lib\ical_encode("{$event->location_street}, {$event->location_city}, {$event->location_province} ($location_name)");

// output event
?>
BEGIN:VEVENT
UID:<?= "$uid_prefix$event_id@$domain" ?>

DTSTAMP:<?= $this->Time->iCal(\Cake\I18n\FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($event->created) ?>

LAST-MODIFIED:<?= $this->Time->iCal($event->created) ?>

DTSTART:<?= $this->Time->iCal($event->start_time) ?>

DTEND:<?= $this->Time->iCal($event->end_time) ?>

LOCATION:<?= $location_address ?>

SUMMARY:<?= \App\Lib\ical_encode($event->name) ?>

DESCRIPTION:<?= __('{0}, at {1}, on {2}',
	\App\Lib\ical_encode($event->name),
	\App\Lib\ical_encode($event->location_name),
	$this->Time->iCalDateTimeRange($event)
);
?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
