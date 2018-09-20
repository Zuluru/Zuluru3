<?php
/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Upload $photo
 * @type boolean $is_me
 * @type boolean $is_relative
 */

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(h($person->full_name));
$this->Html->addCrumb(__('View'));
?>

<div class="people view">
	<h2><?php
	// TODO: Make this a tabbed interface?
	// TODOBOOTSTRAP: Fix photo alignment
	echo $this->element('People/player_photo', ['person' => $person, 'photo' => $photo]);
	echo $person->full_name;
	?></h2>

<?php
$has_visible_contact = false;
$visible_properties = $person->visibleProperties();
$is_player = collection($person->groups)->some(function ($group) { return $group->id == GROUP_PLAYER; });
?>
	<dl class="dl-horizontal">
<?php
if ($person->user_id):
	if (in_array('user_name', $visible_properties)):
?>
		<dt><?= __('Username') ?></dt>
		<dd><?= $person->user_name ?></dd>
<?php
	endif;

	if (in_array('user_id', $visible_properties)):
?>
		<dt><?= __('{0} User Id', Configure::read('feature.manage_name')) ?></dt>
		<dd><?= $person->user_id ?></dd>
<?php
	endif;

	if (in_array('id', $visible_properties)):
?>
		<dt><?= __('Zuluru Id') ?></dt>
		<dd><?= $person->id ?></dd>
<?php
	endif;
endif;

if (in_array('last_login', $visible_properties) && !empty($person->last_login)):
?>
		<dt><?= __('Last Login') ?></dt>
		<dd><?= $this->Time->datetime($person->last_login) ?></dd>
<?php
endif;

if (in_array('client_ip', $visible_properties) && !empty($person->client_ip)):
?>
		<dt><?= __('IP Address') ?></dt>
		<dd><?= $person->client_ip ?></dd>
<?php
endif;

if (in_array('email', $visible_properties) && !empty($person->email)):
?>
		<dt><?= __('Email Address') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $this->Html->link($person->email, "mailto:{$person->email}");
			echo ' (' . ($person->publish_email ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('alternate_email', $visible_properties) && !empty($person->alternate_email)):
?>
		<dt><?= __('Alternate Email Address') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $this->Html->link($person->alternate_email, "mailto:{$person->alternate_email}");
			echo ' (' . ($person->publish_alternate_email ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('home_phone', $visible_properties) && !empty($person->home_phone)):
?>
		<dt><?= __('Phone (home)') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $person->home_phone;
			echo ' (' . ($person->publish_home_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('work_phone', $visible_properties) && !empty($person->work_phone)):
?>
		<dt><?= __('Phone (work)') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $person->work_phone;
			if (!empty($person->work_ext)) {
				echo ' x' . $person->work_ext;
			}
			echo ' (' . ($person->publish_work_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('mobile_phone', $visible_properties) && !empty($person->mobile_phone)):
?>
		<dt><?= __('Phone (mobile)') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $person->mobile_phone;
			echo ' (' . ($person->publish_mobile_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('alternate_full_name', $visible_properties) && !empty($person->alternate_full_name)):
?>
		<dt><?= __('Alternate Contact') ?></dt>
		<dd><?= $person->alternate_full_name ?></dd>
<?php
endif;

if (in_array('alternate_work_phone', $visible_properties) && !empty($person->alternate_work_phone)):
?>
		<dt><?= __('Phone (work)') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $person->alternate_work_phone;
			if (!empty($person->alternate_work_ext)) {
				echo ' x' . $person->alternate_work_ext;
			}
			echo ' (' . ($person->publish_alternate_work_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

if (in_array('alternate_mobile_phone', $visible_properties) && !empty($person->alternate_mobile_phone)):
?>
		<dt><?= __('Phone (mobile)') ?></dt>
		<dd><?php
			$has_visible_contact = true;
			echo $person->alternate_mobile_phone;
			echo ' (' . ($person->publish_alternate_mobile_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
endif;

$label = __('Address');
if (in_array('addr_street', $visible_properties) && !empty($person->addr_street)):
?>
		<dt><?= $label ?></dt>
		<dd><?php
			echo $person->addr_street;
			$label = '&nbsp;';
		?></dd>
<?php
endif;

$addr = [];
if (in_array('addr_city', $visible_properties) && !empty($person->addr_city)) {
	$addr[] = $person->addr_city;
}
if (in_array('addr_prov', $visible_properties) && !empty($person->addr_prov)) {
	$addr[] = __($person->addr_prov);
}
if (in_array('addr_country', $visible_properties) && !empty($person->addr_country)) {
	$addr[] = __($person->addr_country);
}
if (!empty($addr)):
?>
		<dt><?= $label ?></dt>
		<dd><?= implode(', ', $addr) ?></dd>
<?php
	$label = '&nbsp;';
endif;

if (in_array('addr_postalcode', $visible_properties) && !empty($person->addr_postalcode)):
?>
		<dt><?= $label ?></dt>
		<dd><?= $person->addr_postalcode ?></dd>
<?php
endif;

if (in_array('birthdate', $visible_properties)):
?>
		<dt><?= __('Birthdate') ?></dt>
		<dd><?php
			if (Configure::read('feature.birth_year_only')) {
				if (empty($person->birthdate) || $person->birthdate->year == 0) {
					echo __('unknown');
				} else {
					echo $person->birthdate->year;
				}
			} else {
				echo $this->Time->date($person->birthdate);
			}
		?></dd>
<?php
endif;

if (in_array('gender_display', $visible_properties)):
?>
		<dt><?= __('Gender') ?></dt>
		<dd><?= __($person->gender_display) ?>&nbsp;</dd>
<?php
endif;

if (in_array('height', $visible_properties) && !empty($person->height)):
?>
		<dt><?= __('Height') ?></dt>
		<dd><?= $person->height . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm') : __('inches')) ?></dd>
<?php
endif;

if (in_array('shirt_size', $visible_properties) && !empty($person->shirt_size)):
?>
		<dt><?= __('Shirt Size') ?></dt>
		<dd><?= __($person->shirt_size) ?></dd>
<?php
endif;

if (in_array('skills', $visible_properties) && !empty($person->skills)):
	if (Configure::read('profile.skill_level')):
?>
		<dt><?= __('Skill Level') ?></dt>
		<dd><?php
			$sports = [];
			$sport_count = count(Configure::read('options.sport'));
			foreach ($person->skills as $skill) {
				if ($sport_count > 1) {
					$sports[] = Inflector::humanize($skill->sport) . ': ' . __(Configure::read("options.skill.{$skill->skill_level}"));
				} else {
					$sports[] = __(Configure::read("options.skill.{$skill->skill_level}"));
				}
			}
			echo implode('<br />', $sports);
		?></dd>
<?php
	endif;

	if (Configure::read('profile.year_started')):
?>
		<dt><?= __('Year Started') ?></dt>
		<dd><?php
			$sports = [];
			$sport_count = count(Configure::read('options.sport'));
			foreach ($person->skills as $skill) {
				if ($sport_count > 1) {
					$sports[] = Inflector::humanize($skill->sport) . ': ' . $skill->year_started;
				} else {
					$sports[] = $skill->year_started;
				}
			}
			echo implode('<br />', $sports);
		?></dd>
<?php
	endif;
endif;

if (in_array('groups', $visible_properties)):
?>
		<dt><?= __n('Account Class', 'Account Classes', count($person->groups)) ?></dt>
		<dd><?php
			$names = [];
			foreach ($person->groups as $group) {
				$names[] = __($group->name);
			}
			if (empty($names)) {
				echo __('None');
			} else {
				echo implode(', ', $names);
			}
		?></dd>
<?php
endif;

if (in_array('status', $visible_properties)):
?>
		<dt><?= __('Account Status') ?></dt>
		<dd><?= __($person->status) ?></dd>
<?php
endif;

if (in_array('has_dog', $visible_properties)):
?>
		<dt><?= __('Has Dog') ?></dt>
		<dd><?= $person->has_dog ? __('Yes') : __('No') ?></dd>
<?php
endif;

if (in_array('contact_for_feedback', $visible_properties)):
?>
		<dt><?= __('Contact For Feedback') ?></dt>
		<dd><?= $person->contact_for_feedback ? __('Yes') : __('No') ?></dd>
<?php
endif;
?>
	</dl>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if (Configure::read('Perm.is_logged_in') && $has_visible_contact) {
	echo $this->Html->tag('li', $this->Html->link(__('VCF'), ['action' => 'vcf', 'person' => $person->id]));
}
if (Configure::read('Perm.is_logged_in') && Configure::read('feature.annotations')) {
	echo $this->Html->tag('li', $this->Html->link(__('Add Note'), ['action' => 'note', 'person' => $person->id]));
}
if ($is_me || $is_relative || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_24.png', ['action' => 'edit', 'person' => $person->id, 'return' => AppController::_return()], ['alt' => __('Edit Profile'), 'title' => __('Edit Profile')]));
	echo $this->Html->tag('li', $this->Html->link(__('Edit Preferences'), ['action' => 'preferences', 'person' => $person->id]));
	if (!empty($person->user_id)) {
		echo $this->Html->tag('li', $this->Html->link(__('Change Password'), ['controller' => 'Users', 'action' => 'change_password', 'user' => $person->user_id]));
	}
}
if ($is_relative || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
	echo $this->Html->tag('li', $this->Html->link(__('Act As'), ['controller' => 'People', 'action' => 'act_as', 'person' => $person->id]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_24.png', ['action' => 'delete', 'person' => $person->id], ['alt' => __('Delete Player'), 'title' => __('Delete Player')], ['confirm' => __('Are you sure you want to delete this person?')]));
}
?>
	</ul>
</div>

<?php
if (!empty($person->notes)):
?>
<div class="related">
	<h3><?= __('Notes')?></h3>
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
	foreach ($person->notes as $note):
?>
				<tr>
					<td><?php
						echo $this->element('People/block', ['person' => $note->created_person]);
						echo $this->Html->tag('br');
						echo $this->Time->datetime($note->created);
					?></td>
					<td><?= $note->note ?></td>
					<td><?= __(Configure::read("visibility.{$note->visibility}")) ?></td>
					<td class="actions"><?php
						if ($note->created_person_id == Configure::read('Perm.my_id')) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'note', 'note' => $note->id],
								['alt' => __('Edit Note'), 'title' => __('Edit Note')]
							);
						}
						// Admins are the only ones that can see those notes (loaded or not based on
						// conditions in the controller), and they can delete them too.
						if ($note->created_person_id == Configure::read('Perm.my_id') || $note->visibility == VISIBILITY_ADMIN) {
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete_note', 'note' => $note->id],
								['alt' => __('Delete Note'), 'title' => __('Delete Note')],
								['confirm' => __('Are you sure you want to delete this note?')]
							);
						}
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<?php
endif;

if (in_array('related_to', $visible_properties) && AppController::_isChild($person) && !empty($person->related_to)):
?>
<div class="related">
	<h3><?= __('Contacts') ?></h3>
	<dl class="dl-horizontal">
<?php
	foreach ($person->related_to as $relative):
		if ($relative->_joinData->approved):
?>
		<dt><?= __('Name') ?></dt>
		<dd><?= $relative->full_name ?></dd>
<?php
			if (!empty($relative->email)):
?>
		<dt><?= __('Email Address') ?></dt>
		<dd><?php
			echo $this->Html->link($relative->email, "mailto:{$relative->email}");
			echo ' (' . ($relative->publish_email ? __('published') : __('private')) . ')';
		?></dd>
<?php
			endif;

			if (!empty($relative->alternate_email)):
?>
		<dt><?= __('Alternate Email Address') ?></dt>
		<dd><?php
			echo $this->Html->link($relative->alternate_email, "mailto:{$relative->alternate_email}");
			echo ' (' . ($relative->publish_alternate_email ? __('published') : __('private')) . ')';
		?></dd>
<?php
			endif;

			if (in_array('home_phone', $visible_properties) && !empty($relative->home_phone)):
?>
		<dt><?= __('Phone (home)') ?></dt>
		<dd><?php
			echo $relative->home_phone;
			echo ' (' . ($relative->publish_home_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
			endif;

			if (in_array('work_phone', $visible_properties) && !empty($relative->work_phone)):
?>
		<dt><?= __('Phone (work)') ?></dt>
		<dd><?php
			echo $relative->work_phone;
			if (!empty($relative->work_ext)) {
				echo ' x' . $relative->work_ext;
			}
			echo ' (' . ($relative->publish_work_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
			endif;

			if (in_array('mobile_phone', $visible_properties) && !empty($relative->mobile_phone)):
?>
		<dt><?= __('Phone (mobile)') ?></dt>
		<dd><?php
			echo $relative->mobile_phone;
			echo ' (' . ($relative->publish_mobile_phone ? __('published') : __('private')) . ')';
		?></dd>
<?php
			endif;
		endif;
	endforeach;
?>
	</dl>
</div>
<?php
endif;

$all_teams = $this->UserCache->read('AllTeamIDs', $person->id);
if (in_array('teams', $visible_properties) && ($is_player || !empty($all_teams))):
?>
<div class="related">
	<h3><?= __('Teams') ?></h3>
<?php
	if (!empty($person->teams)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
		foreach ($person->teams as $team):
?>
				<tr>
					<td><?php
					echo $this->element('People/roster_role', ['roster' => $team->_matchingData['TeamsPeople'], 'team' => $team, 'division' => $team->division]) .
						' ' . __('on') . ' ' .
						$this->element('Teams/block', ['team' => $team]) .
						' (' . $this->element('Divisions/block', ['division' => $team->division, 'field' => 'long_league_name']) . ')';
					if (!empty($team->division_id)) {
						$positions = Configure::read("sports.{$team->division->league->sport}.positions");
						if (!empty($positions)) {
							echo ' (' . $this->element('People/roster_position', ['roster' => $team->_matchingData['TeamsPeople'], 'team' => $team, 'division' => $team->division]) . ')';
						}
					}
					?></td>
				</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
		echo $this->element('People/roster_div');
	endif;
?>

	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Show Team History'), ['controller' => 'People', 'action' => 'teams', 'person' => $person->id]));
?>
		</ul>
	</div>
</div>
<?php
endif;

if ((in_array('relatives', $visible_properties)) && (!empty($person->relatives) || !empty($person->related_to))):
?>
<div class="related">
	<h3><?= __('Relatives') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Relative') ?></th>
					<th><?= __('Approved') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($person->relatives as $relative):
?>
				<tr>
					<td><?php
						$block = $this->element('People/block', ['person' => $relative]);
						echo $is_me ? __('You can control {0}', $block) : __('{0} can control {1}', $person->first_name, $block);
					?></td>
					<td><?= $relative->_joinData->approved ? __('Yes') : __('No') ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png', ['controller' => 'People', 'action' => 'view', 'person' => $relative->id]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'People', 'action' => 'remove_relative', 'person' => $person->id, 'relative' => $relative->id],
							['alt' => __('Remove'), 'title' => __('Remove Relation')],
							['confirm' => __('Are you sure you want to remove this relation? This does not delete their profile, it only breaks the link between you.')]
						);
					?></td>
				</tr>

<?php
	endforeach;

	foreach ($person->related_to as $relative):
?>
			<tr>
				<td><?php
					$block = $this->element('People/block', ['person' => $relative]);
					echo $is_me ? __('{0} can control you', $block) : __('{0} can control {1}', $block, $person->first_name);
				?></td>
				<td><?= $relative->_joinData->approved ? __('Yes') : __('No') ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png', ['controller' => 'People', 'action' => 'view', 'person' => $relative->id]);
				// Only full users can remove relations
				if (!empty($person->user_id)) {
					echo $this->Form->iconPostLink('delete_24.png',
						['controller' => 'People', 'action' => 'remove_relative', 'person' => $relative->id, 'relative' => $person->id],
						['alt' => __('Remove'), 'title' => __('Remove Relation')],
						['confirm' => __('Are you sure you want to remove this relation? This does not delete their profile, it only breaks the link between you.')]
					);
				}
				if (!$relative->_joinData->approved) {
					echo $this->Form->iconPostLink('approve_24.png', ['controller' => 'People', 'action' => 'approve_relative', 'person' => $relative->id, 'relative' => $person->id]);
				}
				?></td>
			</tr>

<?php
	endforeach;
?>
		</tbody>
	</table>
	</div>

<?php
	if ($is_me):
?>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Link a relative'), ['controller' => 'People', 'action' => 'link_relative']));
?>
		</ul>
	</div>
<?php
	endif;
?>
</div>
<?php
endif;

if (in_array('badges', $visible_properties) && !empty($person->badges)):
?>
<div class="related">
	<h3><?= __('Badges') ?></h3>
	<p><?php
		foreach ($person->badges as $badge) {
			echo $this->Html->iconLink("{$badge->icon}_64.png", ['controller' => 'Badges', 'action' => 'view', 'badge' => $badge->id],
				['alt' => $badge->name, 'title' => $badge->description]);
		}
	?></p>
</div>
<?php
endif;

if (!empty($person->divisions)):
?>
<div class="related">
	<h3><?= __('Divisions') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
	foreach ($person->divisions as $division):
?>
				<tr>
					<td><?php
						echo __(Configure::read("options.division_position.{$division->_matchingData['DivisionsPeople']->position}")) . ' ' . __('of') . ' ' .
							$this->element('Divisions/block', ['division' => $division, 'field' => 'long_league_name'])
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<?php
endif;

if (in_array('allstars', $visible_properties) && !empty($person->allstars)):
?>
<div class="related">
	<h3><?= __('Allstar Nominations') ?></h3>
<?php
	if (!empty($person->allstars)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Team') ?></th>
					<th><?= __('Opponent') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		foreach ($person->allstars as $allstar):
?>
				<tr>
					<td><?= $this->Html->link($this->Time->datetime($allstar->score_entry->game->game_slot->start_time), ['controller' => 'Games', 'action' => 'view', 'game' => $allstar->score_entry->game_id]) ?></td>
					<td><?= $this->element('Teams/block', [
						'team' => $allstar->team_id == $allstar->score_entry->game->home_team_id ? $allstar->score_entry->game->home_team : $allstar->score_entry->game->away_team,
						'show_shirt' => false,
					]) ?></td>
					<td><?= $this->element('Teams/block', [
						'team' => $allstar->team_id == $allstar->score_entry->game->home_team_id ? $allstar->score_entry->game->away_team : $allstar->score_entry->game->home_team,
						'show_shirt' => false,
					]) ?></td>
					<td class="actions"><?= $this->Html->link(__('Delete'), ['controller' => 'Allstars', 'action' => 'delete', 'allstar' => $allstar->id], ['confirm' => __('Are you sure you want to delete this allstar?')]) ?></td>
				</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
	endif;
?>
</div>
<?php
endif;

if (in_array('preregistrations', $visible_properties) || (($is_me || $is_relative) && !empty($person->preregistrations))):
?>
<div class="related">
	<h3><?= __('Preregistrations') ?></h3>
<?php
	if (!empty($person->preregistrations)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Event') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		foreach ($person->preregistrations as $preregistration):
?>
			<tr>
				<td><?= $this->Html->link($preregistration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $preregistration->event->id]) ?></td>
				<td class="actions"><?php
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', 'preregistration' => $preregistration->id],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this preregistration?')])
				?></td>
			</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
	endif;

	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
?>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Add Preregistration'), ['controller' => 'Preregistrations', 'action' => 'add', 'person' => $person->id]));
?>
		</ul>
	</div>
<?php
	endif;
?>
</div>
<?php
endif;

if ((in_array('registrations', $visible_properties)) && !empty($person->registrations)):
?>
<div class="related">
	<h3><?= __('Recent Registrations') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Event') ?></th>
					<th><?= __('Date') ?></th>
					<th><?= __('Payment') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($person->registrations as $registration):
?>
				<tr>
					<td><?= $this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]) ?></td>
					<td><?= $this->Time->date($registration->created) ?></td>
					<td><?= __($registration->payment) ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
	echo $this->Html->tag('li', $this->Html->link(__('Show Registration History'), ['controller' => 'People', 'action' => 'registrations', 'person' => $person->id]));
?>
		</ul>
	</div>
</div>
<?php
endif;

if ((in_array('credits', $visible_properties)) && !empty($person->credits)):
?>
<div class="related">
	<h3><?= __('Available Credits') ?></h3>
	<p><?= __('These credits can be applied to future registrations.') ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Notes') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($person->credits as $credit):
?>
				<tr>
					<td><?= $this->Time->date($credit->created) ?></td>
					<td><?= $this->Number->currency($credit->amount) ?></td>
					<td><?= $this->Number->currency($credit->amount_used) ?></td>
					<td><?= str_replace("\n", '<br />', $credit->notes) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
	echo $this->Html->tag('li', $this->Html->link(__('Show Credit History'), ['controller' => 'People', 'action' => 'credits', 'person' => $person->id]));
?>
		</ul>
	</div>
</div>
<?php
endif;

if (in_array('waivers', $visible_properties)):
?>
<div class="related">
	<h3><?= __('Waivers') ?></h3>
<?php
	if (!empty($person->waivers)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Waiver') ?></th>
					<th><?= __('Signed') ?></th>
					<th><?= __('Valid From') ?></th>
					<th><?= __('Valid Until') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		foreach ($person->waivers as $waiver):
?>
				<tr>
					<td><?= $waiver->name ?></td>
					<td><?= $this->Time->fulldate($waiver->_matchingData['WaiversPeople']->created) ?></td>
					<td><?= $this->Time->fulldate($waiver->_matchingData['WaiversPeople']->valid_from) ?></td>
					<td><?= $this->Time->fulldate($waiver->_matchingData['WaiversPeople']->valid_until) ?></td>
					<td class="actions"><?= $this->Html->iconLink('view_24.png', ['controller' => 'Waivers', 'action' => 'review', 'waiver' => $waiver->id, 'date' => $waiver->_matchingData['WaiversPeople']->valid_from->toDateString()]) ?></td>
				</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
	else:
?>
	<p><?= __('No current waiver is in effect.') ?></p>
<?php
	endif;
?>

	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Show Waiver History'), ['controller' => 'People', 'action' => 'waivers', 'person' => $person->id]));
?>
		</ul>
	</div>
</div>
<?php
endif;

if (in_array('uploads', $visible_properties)):
?>
<div class="related">
	<h3><?= __('Documents') ?></h3>
<?php
	if (!empty($person->uploads)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Document') ?></th>
					<th><?= __('Valid From') ?></th>
					<th><?= __('Valid Until') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		foreach ($person->uploads as $upload):
?>
				<tr>
					<td><?= $upload->upload_type->name ?></td>
<?php
			if ($document->approved):
?>
					<td><?= $this->Time->date($upload->valid_from) ?></td>
					<td><?= $this->Time->date($upload->valid_until) ?></td>
<?php
			else:
?>
					<td colspan="2" class="highlight-message"><?= __('Unapproved') ?></td>
<?php
			endif;
?>
					<td class="actions"><?php
						echo $this->Html->link(__('View'), ['action' => 'document', 'document' => $upload->id], ['target' => 'preview']);
						if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
							if ($upload->approved) {
								echo $this->Html->link(__('Edit'), ['action' => 'edit_document', 'document' => $upload->id, 'return' => AppController::_return()]);
							} else {
								echo $this->Html->link(__('Approve'), ['action' => 'approve_document', 'document' => $upload->id, 'return' => AppController::_return()]);
							}
						}

						echo $this->Jquery->ajaxLink($this->Html->iconImg('delete_24.png', ['alt' => __('Delete'), 'title' => __('Delete')]), [
							'url' => ['action' => 'delete_document', 'document' => $upload->id],
							'confirm' => __('Are you sure you want to delete this document?'),
							'disposition' => 'remove_closest',
							'selector' => 'tr',
						], [
							'escape' => false,
						]);
					?></td>
				</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
	endif;
?>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Upload New Document'), ['action' => 'document_upload', 'person' => $person->id]));
?>
		</ul>
	</div>
</div>
<?php
endif;

if ((in_array('tasks', $visible_properties)) && !empty($person->tasks)):
?>
<div class="related">
	<h3><?= __('Assigned Tasks') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Task') ?></th>
					<th><?= __('Time') ?></th>
					<th><?= __('Report To') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($person->tasks as $task_slot):
?>
				<tr>
					<td class="splash_item"><?php
						echo $this->Html->link($task_slot->task->name, ['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
					?></td>
					<td class="splash_item"><?php
					echo $this->Time->day($task_slot->task_date) . ', ' .
						$this->Time->time($task_slot->task_start) . '-' .
						$this->Time->time($task_slot->task_end)
					?></td>
					<td class="splash_item"><?= $this->element('People/block', ['person' => $task_slot->task->person]) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<?php
endif;
