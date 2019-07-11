<h2><?= __('{0}: {1}', __('Administrator Guide'), __('League Management')) ?></h2>

<h3><?= __('Structure') ?></h3>
<p><?= __('Before starting to set up your leagues, it\'s important to understand the structure {0} is expecting. Following this structure will mean that {0} is optimized to work with you; trying to impose a different structure may result in some awkwardness as you try to do the unexpected.', ZULURU) ?></p>
<p><?= __('Each year has one or more seasons. Each season has one or more leagues. Each league has one or more divisions. Each division has a number of teams.') ?></p>
<p><?= __('Note that, although you may have an identical league in multiple seasons, or in the same season from year to year, in {0} these are set up as different leagues. Similarly, although the same group of people might play under the same team name in more than one league, each instance of this will be a separate team. {0} retains all past history and makes it available to those who look for it; players can scroll back through their past team history to relive glory days or remind themselves of old rivalries.', ZULURU) ?></p>

<h4><?= __('Seasons') ?></h4>
<p><?= __('A season is a period of time during which all of the games in a league will take place.') ?></p>
<p><?= __('In practice, seasons are rather roughly defined, and may have some small overlap with each other. For example, if you have leagues that run from March through May, May through August, and September through December, then March to May might be your spring season, May to August might be summer, and September to December might be fall.') ?></p>

<h4><?= __('Leagues') ?></h4>
<p><?= __('A league is a group of divisions, collected together for display and logistics purposes.') ?></p>
<p><?= __('There is no absolute rule to determine which divisions comprise each league. One very general rule is that any group of divisions that share "substantial similarities" might be grouped in a league. Alternately, if there is a reasonable chance of a team being moved from one division to another, either during a season or between seasons (e.g. teams being promoted or demoted based on performance, or shuffled for geographic or scheduling purposes), then those divisions should be in the same league. Yet another view is that if it is reasonable for a given individual to be on a team in each of two divisions, then those divisions should <strong>not</strong> be in the same league.') ?></p>
<p><?= __('There may be more than one way of grouping divisions into leagues, you should use whatever is most logical for your organization. For example, if you have divisions for Juniors, Adults and "Masters" on each of Monday, Tuesday and Wednesday, you might set this up as Monday, Tuesday and Wednesday leagues, each with a Juniors, Adults and Masters division, or it might make more sense to have Juniors, Adults and Masters leagues, each with a Monday, Tuesday and Wednesday division.') ?></p>

<h4><?= __('Divisions') ?></h4>
<p><?= __('A division is a group of teams who will be scheduled to play against each other.') ?></p>

<h3><?= __('Setup') ?></h3>
<p><?= __('Once you have decided on your structure, start by creating the first league. There are only a handful of settings for leagues, which are shared by all divisions in that league. Once you have created the league, use the {0} "{1}" link from the league list to set up the first division.',
	$this->Html->iconImg('division_add_24.png'), __('Add Division')
) ?></p>
<p><?= __('Once you have one league and one division set up, you can often use the {0} "{1}" or {2} "{3}" links from the league list to very quickly set up a new league or division, with defaults all taken from the first. This way, you often need to change only one or two settings, or even just the name, rather than go through the entire setup again and again. When a new league or division is created this way, there is no link to the original; it is only used to find initial default settings. This means that if you make changes to the original, those changes are not automatically copied to the clone, but must be made manually (if they apply). However, this also means that you can use this clone process for almost all such setup, without fear that settings will inadvertently be altered at some later date by someone changing the original. By the time you have completed your first year of operation, you should have templates for almost every league and division you will ever offer, which you can continue to clone and be confident that the chance of something being configured incorrectly are extremely slim.',
	$this->Html->iconImg('league_clone_24.png'), __('Clone League'),
	$this->Html->iconImg('division_clone_24.png'), __('Clone Division')
) ?></p>

<h3><?= __('Roster Rules') ?></h3>
<p><?= __('If desired, you can add rules that must be met before a player can be added to the roster of a team in a particular division. Most commonly, this is used to enforce age or gender limits, confirm membership, or block people from joining more than one team, but there are other uses as well. See the help for the "roster rule" field for more information; setting up rules is currently a little bit complex, but there are examples provided that will cover most normal situations.') ?></p>

<h3><?= __('Registration Interaction') ?></h3>
<p><?= __('The optional {0} has hooks which allow it to create teams directly in the correct divisions. If registrations are not enabled on your system, coaches and captains will manually create teams, which will be placed in the "{1}" list (found under the {2} menu). It will be your responsibility to manually move each of these teams into the appropriate division.',
	$this->Html->link(__('registration system'), ['action' => 'guide', 'administrator', 'registration']),
	__('Unassigned Teams'), __('Teams')
) ?></p>
