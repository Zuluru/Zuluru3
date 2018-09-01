<?php
use Cake\Core\Configure;

/**
 * @type \App\Model\Entity\Team $team
 * @type bool $is_coordinator
 * @type bool $is_captain
 * @type bool $can_edit_roster
 */

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb(h($team->name));
$this->Html->addCrumb(__('View'));
?>

<div class="teams view">
	<h2><?php
	echo h($team->name);
	if (!empty($team->short_name)) {
		echo " ({$team->short_name})";
	}
	?></h2>
	<dl class="dl-horizontal">
<?php
if (Configure::read('feature.urls') && !empty($team->website)):
?>
		<dt><?= __('Website') ?></dt>
		<dd><?= $this->Html->link($team->website, $team->website) ?></dd>
<?php
endif;

if (Configure::read('feature.twitter') && !empty($team->twitter_user)):
?>
		<dt><?= __('Twitter') ?></dt>
		<dd><?= $this->Html->link("@{$team->twitter_user}", "https://twitter.com/{$team->twitter_user}") ?></dd>
<?php
endif;

if (Configure::read('feature.shirt_colour')):
?>
		<dt><?= __('Shirt Colour') ?></dt>
		<dd><?php
			echo __($team->shirt_colour);
			echo ' ' . $this->Html->help(['action' => 'teams', 'edit', 'shirt_colour']);
		?></dd>
<?php
endif;

if ($team->division_id):
?>
		<dt><?= __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></dd>
<?php
endif;

if (Configure::read('feature.home_field') && !empty($team->home_field_id)):
?>
		<dt><?= __('Home Field') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $team->field, 'display_field' => 'long_name']) ?></dd>
<?php
endif;

if (Configure::read('feature.facility_preference') && !empty($team->facilities)):
?>
		<dt><?= __('Facility Preference') ?></dt>
		<dd><?php
			$facilities = [];
			foreach ($team->facilities as $facility) {
				$facilities[] = $this->Html->link($facility->name, ['controller' => 'Facilities', 'action' => 'view', 'facility' => $facility->id]);
			}
			echo implode(', ', $facilities);
		?></dd>
<?php
endif;

if (Configure::read('feature.region_preference') && !empty($team->region_preference_id)):
?>
		<dt><?= __('Region Preference') ?></dt>
		<dd><?= __($team->region->name) ?></dd>
<?php
endif;
?>
		<dt><?= __('Roster Status') ?></dt>
		<dd><?php
			echo $team->open_roster ? __('Open') : __('Closed');
			echo ' ' . $this->Html->help(['action' => 'teams', 'edit', 'open_roster']);
		?></dd>
<?php
if (Configure::read('feature.attendance')):
?>
		<dt><?= __('Track Attendance') ?></dt>
		<dd><?php
			echo $team->track_attendance ? __('Yes') : __('No');
			echo ' ' . $this->Html->help(['action' => 'teams', 'edit', 'track_attendance']);
		?></dd>
<?php
	if ($team->track_attendance):
?>
		<dt><?= __('Attendance Reminder') ?></dt>
		<dd><?php
			switch ($team->attendance_reminder) {
				case -1:
					echo __('disabled');
					break;

				case 0:
					echo __('day of game');
					break;

				case 1:
					echo __('day before game');
					break;

				default:
					echo __('{0} days before game', $team->attendance_reminder);
					break;
			}
		?></dd>
		<dt><?= __('Attendance Summary') ?></dt>
		<dd><?php
		switch ($team->attendance_summary) {
			case -1:
				echo __('disabled');
				break;

			case 0:
				echo __('day of game');
				break;

			case 1:
				echo __('day before game');
				break;

			default:
				echo __('{0} days before game', $team->attendance_summary);
				break;
		}
		?></dd>
		<dt><?= __('Attendance Notification') ?></dt>
		<dd><?php
		switch ($team->attendance_notification) {
			case -1:
				echo __('disabled');
				break;

			case 0:
				echo __('day of game');
				break;

			case 1:
				echo __('day before game');
				break;

			default:
				echo __('{0} days before game', $team->attendance_notification);
				break;
		}
		?></dd>
<?php
	endif;
endif;

// TODO: Use an element to output this, for greater flexibility. Show the seed where appropriate.
if ($team->division_id && $team->division->schedule_type == 'ratings_ladder'):
?>
		<dt><?= __('Rating') ?></dt>
		<dd><?= $team->rating ?></dd>
<?php
endif;

if (Configure::read('feature.franchises') && !empty($team->franchises)):
?>
		<dt><?= __n('Franchise', 'Franchises', count($team->franchises)) ?></dt>
		<dd><?php
			$franchises = [];
			foreach ($team->franchises as $franchise) {
				$franchises[] = $this->Html->link($franchise->name, ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id]);
			}
			echo implode(', ', $franchises);
		?></dd>
<?php
endif;

if ($team->has('affiliate')):
?>
		<dt><?= __('Affiliated Team') ?></dt>
		<dd><?php
			echo $this->Html->link($team->affiliate->name, ['action' => 'view', 'team' => $team->affiliate->id]) .
				' (' .
				$this->element('Divisions/block', ['division' => $team->affiliate->division, 'field' => 'full_league_name']) .
				')';
		?></dd>
<?php
endif;
?>

	</dl>
</div>

<?php
if (!empty($team->notes)):
?>
<fieldset>
	<legend><?= __('Notes') ?></legend>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('From') ?></th>
					<th><?= __('Note') ?></th>
					<th><?= __('Visibility') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($team->notes as $note):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $note->created_person]) ?><br /><?= $this->Time->datetime($note->created) ?></td>
					<td><?= $note->note ?></td>
					<td><?= __(Configure::read("visibility.{$note->visibility}")) ?></td>
					<td class="actions"><?php
						if ($note->created_person_id == Configure::read('Perm.my_id')) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'note', 'note' => $note->id],
								['alt' => __('Edit Note'), 'title' => __('Edit Note')]
							);
						}
						// Admins and coordinators are the only ones that can see those notes (loaded or
						// not based on conditions in the controller), and they can delete them too.
						if ($note->created_person_id == Configure::read('Perm.my_id') || in_array($note->visibility, [VISIBILITY_ADMIN, VISIBILITY_COORDINATOR])) {
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete_note', 'note' => $note->id],
								['alt' => __('Delete'), 'title' => __('Delete Note')],
								['confirm' => __('Are you sure you want to delete this note?')]);
						}
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
</fieldset>
<?php
endif;
?>

<div class="actions columns">
<?php
$extra = [];
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain) {
	$extra[__('Download')] = [
		'url' => ['action' => 'view', 'team' => $team->id, '_ext' => 'csv'],
	];
	if ($team->division_id && Configure::read('scoring.stat_tracking') && $team->division->league->hasStats()) {
		$extra[__('Stat Sheet')] = [
			'url' => ['action' => 'stat_sheet', 'team' => $team->id],
		];
	}
}

$has_numbers = Configure::read('feature.shirt_numbers') && $team->has('people') && collection($team->people)->some(function ($person) {
	return $person->_joinData->number != null;
});
if (Configure::read('feature.shirt_numbers') && !$has_numbers && $can_edit_roster === true) {
	$extra[__('Jersey Numbers')] = [
		'url' => ['action' => 'numbers', 'team' => $team->id],
	];
}

if (empty($team->division_id)) {
	echo $this->element('Teams/actions', ['team' => $team, 'division' => null, 'league' => null, 'format' => 'list', 'extra' => $extra]);
} else {
	echo $this->element('Teams/actions', ['team' => $team, 'division' => $team->division, 'league' => $team->division->league, 'format' => 'list', 'extra' => $extra]);
}
?>
</div>

<?php
if (is_string($can_edit_roster)):
?>
<p class="warning-message"><?= $can_edit_roster ?></p>
<?php
endif;

if (!empty($team->people) && (Configure::read('Perm.is_logged_in') || Configure::read('feature.public'))):
?>
<div class="related row">
	<div class="column">
<?php
	$cols = 3;
	$warning = false;
	$positions = $team->division_id ? Configure::read("sports.{$team->division->league->sport}.positions") : [];
?>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
<?php
	if ($has_numbers):
?>
						<th><?= __('Number') ?></th>
<?php
		++$cols;
	endif;
?>
						<th><?= __('Name') ?></th>
						<th><?= __('Role') ?></th>
<?php
	if (!empty($positions)):
?>
						<th><?= __('Position') ?></th>
<?php
		++$cols;
	endif;

	$display_gender = $team->display_gender;
	if ($display_gender):
		$column = Configure::read('gender.column');
?>
						<th><?= Configure::read('gender.label') ?></th>
<?php
		++$cols;
	endif;

	if (Configure::read('profile.skill_level')):
?>
						<th><?= __('Rating') ?></th>
<?php
		++$cols;
	endif;

	if (Configure::read('feature.badges') && Configure::read('Perm.is_logged_in')):
?>
						<th><?= __('Badges') ?></th>
<?php
		++$cols;
	endif;
?>
						<th><?= __('Date Joined') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	$roster_count = $skill_count = $skill_total = 0;
	$captains = ['Open' => 0, 'Woman' => 0];
	if ($team->division_id) {
		$roster_required = Configure::read("sports.{$team->division->league->sport}.roster_requirements.{$team->division->ratio_rule}");
	} else {
		$roster_required = 0;
	}
	foreach ($team->people as $person):
		// Maybe add a warning
		if (Configure::read('Perm.is_logged_in') && $person->can_add !== true && !$warning):
			$warning = true;
			$class = ' class="warning-message"';
?>
					<tr>
						<td colspan="<?= $cols ?>"<?= $class ?>><strong>
<?php
			if ($team->division_id && $team->division->is_playoff) {
				$typical_reason = __('the current roster does not meet the playoff roster rules');
			} else if ($team->division_id && Configure::read('feature.registration') && $team->division->flag_membership) {
				$typical_reason = __('they do not have a current membership');
			} else {
				$typical_reason = __('there is something wrong with their account');
			}
			echo __('Notice: The following players are currently INELIGIBLE to participate on this roster. This is typically because {0}. They are not allowed to play with this team until this is corrected. Hover your mouse over the {1} to see the specific reason why.',
				$typical_reason,
				$this->Html->iconImg('help_16.png', ['alt' => '?']));
?>
						</strong></td>
					</tr>
<?php
		endif;

		// TODO: Fix this when we can set sports on unassigned teams
		if ($team->division_id) {
			$skill = collection($person->skills)->firstMatch(['enabled' => true, 'sport' => $team->division->league->sport]);
		} else {
			$skill = null;
		}
		if (in_array($person->_joinData->role, Configure::read('playing_roster_roles')) &&
			$person->_joinData->status == ROSTER_APPROVED)
		{
			++ $roster_count;
			if (!empty($skill)) {
				++$skill_count;
				$skill_total += $skill->skill_level;
			}
		}
		if (in_array($person->_joinData->role, Configure::read('required_roster_roles'))) {
			++$captains[$person->roster_designation];
		}

		if (Configure::read('Perm.is_logged_in')) {
			$conflicts = [];
			if ($team->division_id) {
				if (Configure::read('feature.registration') && $team->division->flag_membership && !$person->is_a_member) {
					$conflicts[] = __('not a member');
				}
				if ($team->division->flag_roster_conflict && $person->roster_conflict) {
					$conflicts[] = __('roster conflict');
				}
				if ($team->division->flag_schedule_conflict && $person->schedule_conflict) {
					$conflicts[] = __('schedule conflict');
				}
			}
		}
?>
					<tr>
<?php
		if ($has_numbers):
?>
						<td><?= $this->element('People/number', ['person' => $person, 'roster' => $person->_joinData, 'division' => $team->division]) ?></td>
<?php
		endif;
?>
						<td><?php
							echo $this->element('People/block', compact('person'));
							if (Configure::read('Perm.is_logged_in') && !empty($conflicts)) {
								echo $this->Html->tag('div',
									'(' . implode(', ', $conflicts) . ')',
									['class' => 'warning-message']);
							}
						?></td>
						<td<?= $warning ? ' class="warning-message"' : '' ?>><?php
							echo $this->element('People/roster_role', ['roster' => $person->_joinData, 'division' => $team->division]);
							if (Configure::read('Perm.is_logged_in') && $person->can_add !== true) {
								echo ' ' . $this->Html->iconImg('help_16.png', ['title' => $this->Html->formatMessage($person->can_add, null, true), 'alt' => '?']);
							}
						?></td>
<?php
		if (!empty($positions)):
?>
						<td><?= $this->element('People/roster_position', ['roster' => $person->_joinData, 'division' => $team->division]) ?></td>
<?php
		endif;

		if ($display_gender):
?>
						<td><?= __($person->$column) ?></td>
<?php
		endif;

		if (Configure::read('profile.skill_level')):
?>
						<td><?= !empty($skill) ? $skill->skill_level : '' ?></td>
<?php
		endif;

		if (Configure::read('feature.badges') && Configure::read('Perm.is_logged_in')):
?>
						<td><?php
							foreach ($person->badges as $badge) {
								if (($badge->visibility == BADGE_VISIBILITY_ADMIN && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) || $badge->visibility == BADGE_VISIBILITY_HIGH) {
									echo $this->Html->iconLink("{$badge->icon}_32.png", ['controller' => 'Badges', 'action' => 'view', 'badge' => $badge->id],
										['alt' => $badge->name, 'title' => $badge->description]);
								}
							}
						?></td>
<?php
		endif;
?>
						<td><?= $this->Time->date($person->_joinData->created) ?></td>
					</tr>

<?php
	endforeach;

	if (Configure::read('profile.skill_level') && $skill_count):
?>
					<tr>
<?php
		if ($has_numbers):
?>
						<td></td>
<?php
		endif;
?>
						<td colspan="<?= 2 + $display_gender + (!empty($positions)) ?>"><?= __('Average Skill Rating') ?></td>
						<td><?= sprintf("%.2f", $skill_total / $skill_count) ?></td>
						<td></td>
<?php
		if (Configure::read('feature.badges') && Configure::read('Perm.is_logged_in')):
?>
						<td></td>
<?php
		endif;
?>
					</tr>
<?php
	endif;
?>
				</tbody>
			</table>
		</div>
	</div>

<?php
	if ((Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain) && $roster_count < $roster_required && !$team->division->roster_deadline_passed):
?>
	<p class="warning-message"><?php
		if ($team->division_id && !$team->division->is_playoff) {
			echo __('This team currently has only {0} full-time players listed. Your team roster must have a minimum of {1} rostered \'regular\' players by the start of your division. For playoffs, your roster must be finalized by the team roster deadline ({2}), and all team members must be listed as a \'regular player\'.',
				$roster_count, $roster_required, $this->Time->date($team->division->rosterDeadline())) . ' ';
		}
		echo __('If an individual has not replied promptly to your request to join, we suggest that you contact them to remind them to respond.');
	?></p>
<?php
	endif;

	if ($team->division_id && ($team->division->is_open || $team->division->open->isFuture()) &&
		(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain) &&
		Configure::read('feature.female_captain') &&
		($captains['Open'] == 0 || $captains['Woman'] == 0) &&
		!in_array($team->division->ratio_rule, ['mens', 'womens'])
	):
?>
	<p class="warning-message"><?= __('Notice: All teams are required to have a minimum of 1 man and 1 woman Captain (or Coach, where applicable) on their team roster. This does not include \'Assistant\' Captains, of which there is no minimum requirement. Your team roster is not considered valid until this corrected. To change a player\'s \'role\' please click on the role next to the player\'s name (e.g. Regular Player) and change to \'Captain\' or \'Coach\'.') ?></p>
<?php
	endif;
?>

</div>
<?php
endif;

if (Configure::read('feature.flickr') && !empty($team->flickr_user) && !empty($team->flickr_set) && !$team->flickr_ban):
?>
<object width="550" height="445"><param name="flashvars" value="offsite=true&lang=es-us&page_show_url=%2Fphotos%2F<?= $team->flickr_user ?>%2Fsets%2F<?= $team->flickr_set ?>%2Fshow%2F&page_show_back_url=%2Fphotos%2F<?= $team->flickr_user ?>%2Fsets%2F<?= $team->flickr_set ?>%2F&set_id=<?= $team->flickr_set ?>&jump_to="></param> <param name="movie" value="https://www.flickr.com/apps/slideshow/show.swf?v=71649"></param> <param name="allowFullScreen" value="true"></param><embed type="application/x-shockwave-flash" src="https://www.flickr.com/apps/slideshow/show.swf?v=71649" allowFullScreen="true" flashvars="offsite=true&lang=es-us&page_show_url=%2Fphotos%2F<?= $team->flickr_user ?>%2Fsets%2F<?= $team->flickr_set ?>%2Fshow%2F&page_show_back_url=%2Fphotos%2F<?= $team->flickr_user ?>%2Fsets%2F<?= $team->flickr_set ?>%2F&set_id=<?= $team->flickr_set ?>&jump_to=" width="550" height="445"></embed></object>
<?php
endif;

echo $this->element('People/number_div');
echo $this->element('People/roster_div');
