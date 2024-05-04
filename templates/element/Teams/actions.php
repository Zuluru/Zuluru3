<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Team $team
 * @var string $format
 * @var string $size
 */
use App\Authorization\ContextResource;
use App\Controller\AppController;
use Cake\Event\Event;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = new ArrayObject();
$more = new ArrayObject();

if ($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
		['alt' => __('View'), 'title' => __('View')]);
}

if ($team->division_id) {
	if ($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'schedule') {
		$links[] = $this->Html->iconLink("schedule_$size.png",
			['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $team->id]],
			['alt' => __('Schedule'), 'title' => __('Schedule')]);
	}
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'standings') {
		$links[] = $this->Html->iconLink("standings_$size.png",
			['controller' => 'Divisions', 'action' => 'standings', '?' => ['division' => $division->id, 'team' => $team->id]],
			['alt' => __('Standings'), 'title' => __('Standings')]);
	}
	if (($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'stats') &&
		isset($league) && $this->Authorize->can('stats', $league)
	) {
		$links[] = $this->Html->iconLink("summary_$size.png",
			['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]],
			['alt' => __('Stats'), 'title' => __('View Team Stats')]);
	}
}

if ($this->Authorize->can('add_event', $team)) {
	$more[__('Add a Team Event')] = [
		'url' => ['controller' => 'TeamEvents', 'action' => 'add', '?' => ['team' => $team->id]],
	];
}

if (($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'attendance') &&
	$this->Authorize->can('attendance', $team)
) {
	$links[] = $this->Html->iconLink("attendance_$size.png",
		['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $team->id]],
		['alt' => __('Attendance'), 'title' => __('View Season Attendance Report')]);
}

if ($this->Authorize->can('roster_request', new ContextResource($team, ['division' => isset($division) ? $division : null]))) {
	$more[__('Join Team')] = [
		'url' => ['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]],
	];
}

if (($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'edit') &&
	$this->Authorize->can('edit', $team)
) {
	$more[__('Edit Team')] = [
		'url' => ['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id, 'return' => AppController::_return()]],
	];
}

if (($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'emails') &&
	$this->Authorize->can('emails', $team)
) {
	$more[__('Player Emails')] = [
		'url' => ['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]],
	];
}

if (($this->getRequest()->getParam('controller') != 'Teams' || $this->getRequest()->getParam('action') != 'add_player') &&
	$this->Authorize->can('add_player', new ContextResource($team, ['division' => isset($division) ? $division : null]))
) {
	$more[__('Add Player')] = [
		'url' => ['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]],
	];
}

if ($this->Authorize->can('spirit', $team)) {
	$more[__('Spirit')] = [
		'url' => ['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $team->id]],
	];
}

if ($this->Authorize->can('move', $team)) {
	$more[__('Move Team')] = [
		'url' => ['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]],
	];
}

if  ($this->Authorize->can('delete', $team)) {
	$url = ['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]];
	if ($this->getRequest()->getParam('controller') != 'Teams') {
		$url['?']['return'] = AppController::_return();
	}
	$more[__('Delete')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this team?'),
		'method' => 'post',
	];
}

if ($this->Authorize->can('note', $team)) {
	$more[__('Add Note')] = [
		'url' => ['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
	];
}

$plugin_event = new Event('Plugin.actions.team.links', $this, [$links, $more, $this->Authorize, $this->Html, $team, isset($division) ? $division : null]);
$this->getEventManager()->dispatch($plugin_event);

if (!empty($extra_links)) {
	foreach ((array)$extra_links as $key => $link) {
		if (is_numeric($key)) {
			$links[] = $link;
		} else {
			$links[$key] = $link;
		}
	}
}

if (!empty($extra_more)) {
	foreach ((array)$extra_more as $key => $link) {
		if (is_numeric($key)) {
			$more[] = $link;
		} else {
			$more[$key] = $link;
		}
	}
}

if ($more->count() != 0) {
	$links[] = $this->Jquery->moreWidget(['type' => "team_actions_{$team->id}"], $more->getArrayCopy());
}

if ($format == 'links') {
	echo implode("\n", $links->getArrayCopy());
} else {
	echo $this->Bootstrap->navPills($links->getArrayCopy());
}
