<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var string $uid_prefix
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use function App\Lib\ical_encode;

// Get domain URL for signing events
$domain = Configure::read('App.domain');

$location_name = strtr($event->location_name, '()', '[]');
$location_address = ical_encode("{$event->location_street}, {$event->location_city}, {$event->location_province} ($location_name)");

// output event
?>
BEGIN:VEVENT
UID:<?= "{$uid_prefix}{$event->id}@$domain" ?>

DTSTAMP:<?= $this->Time->iCal(FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($event->created) ?>

LAST-MODIFIED:<?= $this->Time->iCal($event->created) ?>

DTSTART:<?= $this->Time->iCal($event->start_time) ?>

DTEND:<?= $this->Time->iCal($event->end_time) ?>

LOCATION:<?= $location_address ?>

SUMMARY:<?= ical_encode($event->name) ?>

DESCRIPTION:<?= __('{0}, at {1}, on {2}',
	ical_encode($event->name),
	ical_encode($event->location_name),
	$this->Time->iCalDateTimeRange($event)
);
?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
