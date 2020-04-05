<?php
use Cake\Core\Configure;
?>

<p><?= __('The {0} Availability Report is used by coordinators over the course of the season to assist in ensuring that premier {1} and time slots are being fully utilized.',
	Configure::read('UI.field_cap'), Configure::read('UI.fields')
) ?></p>
<p><?= __('After selecting a date for which you want to see the report, it will show a list of all game slots available to this division, and whether or not they are assigned. If assigned, it will show home and away teams{0}.',
	(Configure::read('feature.region_preference') ? __(', and the regional preference of the home team') : '')
) ?>.</p>
<p><?= __('You can also access this report for a particular date through direct links on the division schedule.') ?></p>
