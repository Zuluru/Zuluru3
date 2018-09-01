<h4><?= __('Type: Data') ?></h4>
<p><?= __('The {0} rule accepts a comma-separated list of league ids and returns a count of how many teams the player is/was on that play/played in those leagues.', 'LEAGUE_TEAM_COUNT') ?></p>
<p><?= __('By default, only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted. You can also include teams where the player is listed as a substitute by including \'{0}\' anywhere in the league id list.', 'include_subs') ?></p>
<p><?= __('Note that this looks at all divisions within the specified leagues.') ?></p>
<p><?= __('Example:') ?></p>
<pre>LEAGUE_TEAM_COUNT(123)</pre>
<p><?= __('would return the number of teams playing in league #123 that the player is on.') ?></p>
<pre>LEAGUE_TEAM_COUNT(123,124)</pre>
<p><?= __('would return the number of teams playing in leagues #123 or 124 that the player is on.') ?></p>
<pre>LEAGUE_TEAM_COUNT(123,124,include_subs)</pre>
<p><?= __('would return the number of teams playing in leagues #123 or 124 that the player is on, even as a substitute.') ?></p>
