<?php

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use Cake\Core\Configure;

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
	$links = $more = [];
}

if (!$collapse) {
	if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'view') {
		$links[] = $this->Html->iconLink("view_$size.png",
			['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id],
			['alt' => __('Details'), 'title' => __('View Division Details')]);
	}

	if ($division->schedule_type != 'none') {
		if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'schedule') {
			$links[] = $this->Html->iconLink("schedule_$size.png",
				['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division->id],
				['alt' => __('Schedule'), 'title' => __('Schedule')]);
		}
		if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'standings') {
			$links[] = $this->Html->iconLink("standings_$size.png",
				['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id],
				['alt' => __('Standings'), 'title' => __('Standings')]);
		}
	}
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'stats') &&
	$this->Authorize->can('stats', $league)
) {
	$links[] = $this->Html->iconLink("stats_$size.png",
		['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id],
		['alt' => __('Stats'), 'title' => __('Stats')]);
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'scores') &&
	$this->Authorize->can('scores', $division)
) {
	$more[__('Scores')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id]
	];
}

if ($this->Authorize->can('edit', $division)) {
	if (!$collapse && ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'edit')) {
		$more[__('Edit Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'edit', 'division' => $division->id, 'return' => AppController::_return()],
		];
	}
	if (!empty($division->is_playoff) && ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'initialize_ratings')) {
		$more[__('Initialize Ratings')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($division->schedule_type == 'tournament' && ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'seeds')) {
		$more[__('Initialize Seeds')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id],
		];
	}
	if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'emails') {
		$more[__('Coach/Captain Emails')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id],
		];
	}
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'approve_scores') &&
	$this->Authorize->can('approve_scores', $division)
) {
	$more[__('Approve Scores')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id, 'return' => $return],
	];
}

if ($this->Authorize->can('edit_schedule', $division)) {
	if (($this->request->getParam('controller') != 'Schedules' || $this->request->getParam('action') != 'add')) {
		$more[__('Add Games')] = [
			'url' => ['controller' => 'Schedules', 'action' => 'add', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'fields') {
		$more[__('{0} Distribution Report', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
			'url' => ['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id],
		];
	}
	if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'slots') {
		$more[__('{0} Availability', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
			'url' => ['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id],
		];
	}
	if ($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'status') {
		$more[__('Status Report')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id],
		];
	}
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'spirit') &&
	$this->Authorize->can('spirit', new ContextResource($division, ['league' => $league]))
) {
	$more[__('Division Spirit Report')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id],
	];
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'add_teams') &&
	$this->Authorize->can('add_teams', $division)
) {
	$more[__('Add Teams')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id, 'return' => $return],
	];
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'add_coordinator') &&
	$this->Authorize->can('add_coordinator', $division)
) {
	$more[__('Add Coordinator')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id, 'return' => $return],
	];
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'add') &&
	$this->Authorize->can('add_division', $league)
) {
	$more[__('Clone Division')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'add', 'league' => $division->league_id, 'division' => $division->id, 'return' => $return],
	];
}

if (($this->request->getParam('controller') != 'Divisions' || $this->request->getParam('action') != 'allstars') &&
	$this->Authorize->can('allstars', $division)
) {
	$more[__('Allstars')] = [
		'url' => ['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id],
	];
}

if (!$collapse && $this->Authorize->can('delete', $division)) {
	$url = ['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id];
	if ($this->request->getParam('controller') != 'Divisions') {
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
	$more = array_merge($more, $league_obj->links($division, $this->Authorize->getIdentity(), $this->request->getParam('controller'), $this->request->getParam('action')));
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$more = array_merge($more, $extra);
	} else {
		$more[] = $extra;
	}
}

if ($collapse && !$from_league_actions) {
	echo $this->element('Leagues/actions', array_merge(
		compact('league', 'format', 'size', 'collapse', 'return'),
		[
			'from_division_actions' => true,
			'extra' => $links,
			'more' => $more,
		]
	));
} else {
	$links[] = $this->Jquery->moreWidget(['type' => "division_actions_{$division->id}"], $more);
	if ($format == 'links') {
		echo implode("\n", $links);
	} else {
		echo $this->Html->nestedList($links, ['class' => 'nav nav-pills']);
	}
}
