<?php
use App\Controller\AppController;
use Cake\Core\Configure;

if (!isset($is_coordinator)) {
	$is_coordinator = false;
}
if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}
if (!isset($collapse)) {
	// TODO: Implement counter cache for the number of divisions in a league, so it's more universally available
	$collapse = $league->has('divisions') && (count($league->divisions) == 1);
}
if (!isset($return)) {
	$return = false;
}
if (!isset($from_division_actions)) {
	$from_division_actions = false;
}
if (!isset($more)) {
	$more = [];
}

$links = [];

if (!isset($tournaments)) {
	$tournaments = false;
}
$controller = ($tournaments ? 'Tournaments' : 'Leagues');
$model = ($tournaments ? 'tournament' : 'league');

if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => $controller, 'action' => 'view', $model => $league->id],
		['alt' => __('Details'), 'title' => $tournaments ? __('View Tournament Details') : __('View League Details')]);
}
$schedule_types = array_unique(collection($league->divisions)->extract('schedule_type')->toList());
if (!empty($schedule_types) && ($schedule_types[0] != 'none' || count($schedule_types) > 1)) {
	if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'schedule') {
		$links[] = $this->Html->iconLink("schedule_$size.png",
			['controller' => $controller, 'action' => 'schedule', $model => $league->id],
			['alt' => __('Schedule'), 'title' => __('Schedule')]);
	}
	if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'standings') {
		$links[] = $this->Html->iconLink("standings_$size.png",
			['controller' => $controller, 'action' => 'standings', $model => $league->id],
			['alt' => __('Standings'), 'title' => __('Standings')]);
	}
}
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || ($collapse && $is_coordinator)) {
	if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'edit') {
		$more[$tournaments ? __('Edit Tournament') : __('Edit League')] = [
			'url' => ['controller' => $controller, 'action' => 'edit', $model => $league->id, 'return' => AppController::_return()],
		];
	}
	if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'add') {
		$more[$tournaments ? __('Clone Tournament') : __('Clone League')] = [
			'url' => ['controller' => $controller, 'action' => 'add', $model => $league->id, 'return' => $return],
		];
	}
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'add') {
		$more[__('Add Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'add', 'league' => $league->id, 'return' => $return],
		];
	}

	$url = ['controller' => $controller, 'action' => 'delete', $model => $league->id];
	if ($this->request->params['controller'] != 'Leagues') {
		$url['return'] = AppController::_return();
	}
	$more[$tournaments ? __('Delete Tournament') : __('Delete League')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this league?'),
		'method' => 'post',
	];

	if ($this->request->params['controller'] != 'Leagues' || $this->request->params['action'] != 'participation') {
		$more[__('Participation Report')] = [
			'url' => ['controller' => $controller, 'action' => 'participation', $model => $league->id]
		];
	}
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$links = array_merge($links, $extra);
	} else {
		$links[] = $extra;
	}
}

if ($collapse && !$from_division_actions) {
	echo $this->element('Divisions/actions', array_merge(
		compact('league', 'format', 'size', 'collapse', 'return', 'tournaments'),
		[
			'from_league_actions' => true,
			'division' => $league->divisions[0],
			'league_actions' => $links,
			'league_more' => $more,
		]
	));
} else {
	$links[] = $this->Jquery->moreWidget(['type' => "league_actions_{$league->id}"], $more);
	if ($format == 'links') {
		echo implode("\n", $links);
	} else {
		echo $this->Html->nestedList($links, ['class' => 'nav nav-pills']);
	}
}
