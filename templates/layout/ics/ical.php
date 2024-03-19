<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

// This can happen when an invalid game or team is requested
if (!isset($calendar_type)) {
	return;
}

$short = Configure::read('organization.short_name');

// Prevent caching
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Zuluru//<?= $calendar_type ?>//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:<?= \App\Lib\ical_encode($calendar_name) ?> from <?= $short ?>

<?= $this->fetch('content') ?>
END:VCALENDAR
