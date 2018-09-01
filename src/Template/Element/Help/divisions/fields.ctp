<?php
use Cake\Core\Configure;
?>

<p><?= __('The {0} Distribution Report is used by coordinators over the course of the season to ensure that {1} and time slots are being assigned to teams in a balanced way. There are typically some {1} or time slots that are preferred over others, and this report helps to ensure that everyone gets their fair share of these preferred options.',
	__(Configure::read('UI.field_cap')), __(Configure::read('UI.fields'))
) ?></p>
<p><?= __('The report summarizes all {0} at a single facility at the same time. If the league in question has games in more than one region, sub-totals are also provided for each region.',
	__(Configure::read('UI.fields'))
) ?></p>
<p><?= __('By default, the report includes all games, published or not, but sometimes during the scheduling process it is useful to be able to eliminate the games currently being scheduled from the totals, so there is a link at the top of the report to include only published games.') ?></p>
