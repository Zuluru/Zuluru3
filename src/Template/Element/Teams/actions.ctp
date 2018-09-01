<?php
use App\Controller\AppController;
use Cake\Core\Configure;

if (!isset($is_captain)) {
	$is_captain = false;
}
if (!isset($is_team_manager)) {
	$is_team_manager = Configure::read('Perm.is_manager');
}
if (!isset($is_coordinator)) {
	$is_coordinator = false;
}
if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = $more = [];

if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Teams', 'action' => 'view', 'team' => $team->id],
		['alt' => __('View'), 'title' => __('View')]);
}

if ($team->division_id) {
	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'schedule') {
		$links[] = $this->Html->iconLink("schedule_$size.png",
			['controller' => 'Teams', 'action' => 'schedule', 'team' => $team->id],
			['alt' => __('Schedule'), 'title' => __('Schedule')]);
	}
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'standings') {
		$links[] = $this->Html->iconLink("standings_$size.png",
			['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id, 'team' => $team->id],
			['alt' => __('Standings'), 'title' => __('Standings')]);
	}
	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'stats') {
		if ((Configure::read('Perm.is_logged_in') || Configure::read('feature.public')) && Configure::read('scoring.stat_tracking') && isset($league) && $league->hasStats()) {
			$links[] = $this->Html->iconLink("summary_$size.png",
				['controller' => 'Teams', 'action' => 'stats', 'team' => $team->id],
				['alt' => __('Stats'), 'title' => __('View Team Stats')]);
		}
	}
}
if (Configure::read('feature.attendance') && $team['track_attendance']) {
	if (Configure::read('Perm.is_admin') || $is_team_manager || $is_captain) {
		$more[__('Add a Team Event')] = [
			'url' => ['controller' => 'TeamEvents', 'action' => 'add', 'team' => $team->id],
		];
	}

	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'attendance') {
		if (in_array($team->id, $this->UserCache->read('AllTeamIDs')) || in_array($team->id, $this->UserCache->read('AllRelativeTeamIDs'))) {
			$links[] = $this->Html->iconLink("attendance_$size.png",
				['controller' => 'Teams', 'action' => 'attendance', 'team' => $team->id],
				['alt' => __('Attendance'), 'title' => __('View Season Attendance Report')]);
		}
	}
}
if (Configure::read('Perm.is_logged_in') && $team->open_roster && $team->division_id && !$division->roster_deadline_passed &&
	in_array(GROUP_PLAYER, $this->UserCache->read('GroupIDs')) && !in_array($team->id, $this->UserCache->read('TeamIDs'))
) {
	$more[__('Join Team')] = [
		'url' => ['controller' => 'Teams', 'action' => 'roster_request', 'team' => $team->id],
	];
}
if (Configure::read('Perm.is_admin') || $is_team_manager || $is_captain) {
	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'edit') {
		$more[__('Edit Team')] = [
			'url' => ['controller' => 'Teams', 'action' => 'edit', 'team' => $team->id, 'return' => AppController::_return()],
		];
	}
	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'emails') {
		$more[__('Player Emails')] = [
			'url' => ['controller' => 'Teams', 'action' => 'emails', 'team' => $team->id],
		];
	}
}
if (Configure::read('Perm.is_admin') || $is_team_manager || (($is_captain || $is_coordinator) && !$division->roster_deadline_passed)) {
	if ($this->request->params['controller'] != 'Teams' || $this->request->params['action'] != 'add_player') {
		$more[__('Add Player')] = [
			'url' => ['controller' => 'Teams', 'action' => 'add_player', 'team' => $team->id],
		];
	}
}
if ((Configure::read('Perm.is_admin') || $is_team_manager || $is_coordinator) && isset($league) && $league->hasSpirit()) {
	$more[__('Spirit')] = [
		'url' => ['controller' => 'Teams', 'action' => 'spirit', 'team' => $team->id],
	];
}
if (Configure::read('Perm.is_admin') || $is_team_manager) {
	$more[__('Move Team')] = [
		'url' => ['controller' => 'Teams', 'action' => 'move', 'team' => $team->id],
	];

	$url = ['controller' => 'Teams', 'action' => 'delete', 'team' => $team->id];
	if ($this->request->params['controller'] != 'Teams') {
		$url['return'] = AppController::_return();
	}
	$more[__('Delete')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this team?'),
		'method' => 'post',
	];
}
if (Configure::read('Perm.is_logged_in') && Configure::read('feature.annotations')) {
	$more[__('Add Note')] = [
		'url' => ['controller' => 'Teams', 'action' => 'note', 'team' => $team->id],
	];
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$more = array_merge($more, $extra);
	} else {
		$more[] = $extra;
	}
}

$links[] = $this->Jquery->moreWidget(['type' => "team_actions_{$team->id}"], $more);
if ($format == 'links') {
	echo implode("\n", $links);
} else {
	echo $this->Html->nestedList($links, ['class' => 'nav nav-pills']);
}
