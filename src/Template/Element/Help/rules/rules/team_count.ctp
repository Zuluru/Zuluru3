<?php
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Data') ?></h4>
<p><?= __('The {0} rule accepts a YYYY-MM-DD formatted date and returns a count of how many teams the player is/was on that play/played in leagues that are/were open on this date. It can also accept date ranges in three forms:', 'TEAM_COUNT') ?></p>
<ul>
<li><?= __('YYYY-MM-DD,YYYY-MM-DD: Counts teams that played at any time between the dates specified (inclusive)') ?></li>
<li>&lt;<?= __('YYYY-MM-DD: Counts teams that played at any time up to and including the date specified (equivalent to 0000-00-00,YYYY-MM-DD)') ?></li>
<li>&gt;<?= __('YYYY-MM-DD: Counts teams that played at any time starting from the date specified (equivalent to YYYY-MM-DD,9999-12-31)') ?></li>
</ul>
<p><?= __('The date specification must be enclosed in quotes.') ?></p>
<p><?= __('By default, only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted. You can also include teams where the player is listed as a substitute by including \'include_subs\' after the date.') ?></p>
<p><?= __('Example:') ?></p>
<pre>TEAM_COUNT('<?= $year ?>-06-01')</pre>
<p><?= __('would return the number of teams playing in the summer of {0} that the player is on.', $year) ?></p>
<pre>TEAM_COUNT('&lt;<?= $year ?>-06-01')</pre>
<p><?= __('would return the number of teams that played in the summer of {0} or before that the player is on.', $year) ?></p>
<pre>TEAM_COUNT('<?= $year-5 ?>-06-01,<?= $year ?>-06-01')</pre>
<p><?= __('would return the number of teams that played in the 5 year span up to and including June 1 of this year that the player is on.') ?></p>
<pre>TEAM_COUNT('<?= $year ?>-06-01',include_subs)</pre>
<p><?= __('would return the number of teams playing in the summer of {0} that the player is on, even as a substitute.', $year) ?></p>
