<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
 * @type $format string
 * @type $league_actions string[]
 * @type $league_more string[]
 * @type $size int
 * @type $collapse boolean
 * @type $return boolean
 */

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}
if (!isset($collapse)) {
	$collapse = false;
}
if (!isset($return)) {
	$return = false;
}
if (!isset($from_league_actions)) {
	$from_league_actions = false;
}
if ($from_league_actions) {
	$links = $league_actions;
	$more = $league_more;
} else {
	$links = new ArrayObject();
	$more = new ArrayObject();
}

if (!$collapse) {
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'view') {
		$links[] = $this->Html->iconLink("view_$size.png",
			['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id],
			['alt' => __('Details'), 'title' => __('View Division Details')]);
	}

	if ($division->schedule_type != 'none') {
		if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'schedule') {
			$links[] = $this->Html->iconLink("schedule_$size.png",
				['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division->id],
				['alt' => __('Schedule'), 'title' => __('Schedule')]);
		}
		if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'standings') {
			$links[] = $this->Html->iconLink("standings_$size.png",
				['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id],
				['alt' => __('Standings'), 'title' => __('Standings')]);
		}
	}
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'stats') &&
	$this->Authorize->can('stats', $league)
) {
	$links[] = $this->Html->iconLink("stats_$size.png",
		['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id],
		['alt' => __('Stats'), 'title' => __('Stats')]);
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'scores') &&
	$this->Authorize->can('scores', $division)
) {
	$more[__('Scores')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id]
	];
}

if ($this->Authorize->can('edit', $division)) {
	if (!$collapse && ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'edit')) {
		$more[__('Edit Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'edit', 'division' => $division->id, 'return' => AppController::_return()],
		];
	}
	if (!empty($division->is_playoff) && ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'initialize_ratings')) {
		$more[__('Initialize Ratings')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($division->schedule_type == 'tournament' && ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'seeds')) {
		$more[__('Initialize Seeds')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id],
		];
	}
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'emails') {
		$more[__('Coach/Captain Emails')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id],
		];
	}
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'approve_scores') &&
	$this->Authorize->can('approve_scores', $division)
) {
	$more[__('Approve Scores')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id, 'return' => $return],
	];
}

if ($this->Authorize->can('edit_schedule', $division)) {
	if (($this->getRequest()->getParam('controller') != 'Schedules' || $this->getRequest()->getParam('action') != 'add')) {
		$more[__('Add Games')] = [
			'url' => ['controller' => 'Schedules', 'action' => 'add', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'fields') {
		$more[__('{0} Distribution Report', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
			'url' => ['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id],
		];
	}
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'slots') {
		$more[__('{0} Availability', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
			'url' => ['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id],
		];
	}
	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'status') {
		$more[__('Status Report')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id],
		];
	}
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'spirit') &&
	$this->Authorize->can('spirit', new ContextResource($division, ['league' => $league]))
) {
	$more[__('Division Spirit Report')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id],
	];
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'add_teams') &&
	$this->Authorize->can('add_teams', $division)
) {
	$more[__('Add Teams')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id, 'return' => $return],
	];
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'add_coordinator') &&
	$this->Authorize->can('add_coordinator', $division)
) {
	$more[__('Add Coordinator')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id, 'return' => $return],
	];
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'add') &&
	$this->Authorize->can('add_division', $league)
) {
	$more[__('Clone Division')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add', 'league' => $division->league_id, 'division' => $division->id, 'return' => $return],
	];
}

if (($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'allstars') &&
	$this->Authorize->can('allstars', $division)
) {
	$more[__('Allstars')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id],
	];
}

if (!$collapse && $this->Authorize->can('delete', $division)) {
	$url = ['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id];
	if ($this->getRequest()->getParam('controller') != 'Divisions') {
		$url['return'] = AppController::_return();
	}
	$more[__('Delete Division')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this division?'),
		'method' => 'post',
	];
}

// Some items are only applicable depending on league configuration
if (!empty($division->schedule_type)) {
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$division->schedule_type}");
	foreach ($league_obj->links($division, $this->Authorize->getIdentity(), $this->getRequest()->getParam('controller'), $this->getRequest()->getParam('action')) as $key => $link) {
		if (is_numeric($key)) {
			$more[] = $link;
		} else {
			$more[$key] = $link;
		}
	}
}

$plugin_event = new Event('Plugin.actions.division.links', $this, [$links, $more, $this->Authorize, $this->Html, $division]);
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

if ($collapse && !$from_league_actions) {
	echo $this->element('Leagues/actions', array_merge(
		compact('league', 'format', 'size', 'collapse', 'return'),
		[
			'from_division_actions' => true,
			'extra_links' => $links,
			'extra_more' => $more,
		]
	));
} else {
	if ($more->count() != 0) {
		$links[] = $this->Jquery->moreWidget(['type' => "division_actions_{$division->id}"], $more->getArrayCopy());
	}

	if ($format == 'links') {
		echo implode("\n", $links->getArrayCopy());
	} else {
		echo $this->Html->nestedList($links->getArrayCopy(), ['class' => 'nav nav-pills']);
	}
}
