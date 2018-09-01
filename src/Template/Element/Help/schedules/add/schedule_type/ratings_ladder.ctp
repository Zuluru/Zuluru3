<?php
use Cake\Core\Configure;
?>

<h3><?= __('Single blank, unscheduled game') ?></h3>
<p><?= __('Creates a single game on a chosen date. No teams or {0} are assigned.', __(Configure::read('UI.field'))) ?></p>
<h3><?= __('Set of ratings-scheduled games for all teams') ?></h3>
<p><?= __('Creates enough games to schedule the entire ladder on a chosen date. Teams are assigned according to the ladder algorithm and {0} assigned according to settings.', __(Configure::read('UI.fields'))) ?></p>
<p><?= __('For divisions with games at diverse facilities or times (e.g. anything where players are likely to have to make different plans for transportation, child care, etc. depending on where/when they are scheduled to play), common usage is to schedule the first two games of the season immediately, then schedule game three once results from game one have been received, etc., so that players always have at least a week to make plans.') ?></p>
<p><?= __('For divisions where this is not a concern, it can be preferable to wait until results from game one have been received before scheduling game two, etc. This will allow the ladder to reshape itself more quickly, providing slightly better matchups on average.') ?></p>
<p><?= __('Scheduling three games in advance should be avoided whenever possible. It is marginally acceptable in the middle of the season, when the ladder has achieved some level of stability but before the final playoff standings are being locked in.') ?></p>
