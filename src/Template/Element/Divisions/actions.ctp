<?php
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use Cake\Core\Configure;

if (!isset($is_league_manager)) {
	$is_league_manager = Configure::read('Perm.is_manager');
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
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'view') {
		$links[] = $this->Html->iconLink("view_$size.png",
			['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id],
			['alt' => __('Details'), 'title' => __('View Division Details')]);
	}

	if ($division->schedule_type != 'none') {
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'schedule') {
			$links[] = $this->Html->iconLink("schedule_$size.png",
				['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division->id],
				['alt' => __('Schedule'), 'title' => __('Schedule')]);
		}
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'standings') {
			$links[] = $this->Html->iconLink("standings_$size.png",
				['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id],
				['alt' => __('Standings'), 'title' => __('Standings')]);
		}
	}
}

if ($division->schedule_type != 'none') {
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'stats') {
		if ((Configure::read('Perm.is_logged_in') || Configure::read('feature.public')) && Configure::read('scoring.stat_tracking') && $league->hasStats()) {
			$links[] = $this->Html->iconLink("stats_$size.png",
				['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id],
				['alt' => __('Stats'), 'title' => __('Stats')]);
		}
	}
	if ($division->schedule_type != 'competition') {
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'scores') {
			if (Configure::read('Perm.is_logged_in') || Configure::read('feature.public')) {
				$more[__('Scores')] = [
					'url' => ['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id]
				];
			}
		}
	}
}

if (Configure::read('Perm.is_admin') || $is_league_manager || $is_coordinator) {
	if (!$collapse && ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'edit')) {
		$more[__('Edit Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'edit', 'division' => $division->id, 'return' => AppController::_return()],
		];
	}
	if (!empty($division->is_playoff) && ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'initialize_ratings')) {
		$more[__('Initialize Ratings')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($division->schedule_type == 'tournament' && ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'seeds')) {
		$more[__('Initialize Seeds')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id],
		];
	}
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'emails') {
		$more[__('Coach/Captain Emails')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id],
		];
	}
	if ($division->schedule_type != 'none') {
		if ($division->schedule_type != 'competition') {
			if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'approve_scores') {
				$more[__('Approve scores')] = [
					'url' => ['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id, 'return' => $return],
				];
			}
		}
		if ($this->request->params['controller'] != 'Schedules' || $this->request->params['action'] != 'add') {
			$more[__('Add Games')] = [
				'url' => ['controller' => 'Schedules', 'action' => 'add', 'division' => $division->id, 'return' => $return],
			];
		}
		if ($league->hasSpirit() && ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'spirit')) {
			$more[__('Division Spirit Report')] = [
				'url' => ['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id],
			];
		}
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'fields') {
			$more[__('{0} Distribution Report', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
				'url' => ['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id],
			];
		}
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'slots') {
			$more[__('{0} Availability', __(Configure::read("sports.{$league->sport}.field_cap")))] = [
				'url' => ['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id],
			];
		}
		if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'status') {
			$more[__('Status Report')] = [
				'url' => ['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id],
			];
		}
	}
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'add_teams') {
		$more[__('Add Teams')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id, 'return' => $return],
		];
	}
}
if (Configure::read('Perm.is_admin') || $is_league_manager) {
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'add_coordinator') {
		$more[__('Add Coordinator')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id, 'return' => $return],
		];
	}
	if ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'add') {
		$more[__('Clone Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'add', 'league' => $division->league_id, 'division' => $division->id, 'return' => $return],
		];
	}
	if ($division['schedule_type'] != 'none') {
		if ($division->allstars != 'never' && ($this->request->params['controller'] != 'Divisions' || $this->request->params['action'] != 'allstars')) {
			$more[__('Allstars')] = [
				'url' => ['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id],
			];
		}
	}
	if (!$collapse) {
		$url = ['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id];
		if ($this->request->params['controller'] != 'Divisions') {
			$url['return'] = AppController::_return();
		}
		$more[__('Delete Division')] = [
			'url' => $url,
			'confirm' => __('Are you sure you want to delete this division?'),
			'method' => 'post',
		];
	}
}

// Some items are only applicable depending on league configuration
if (!empty($division->schedule_type)) {
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$division->schedule_type}");
	$more = array_merge($more, $league_obj->links($division, Configure::read('Perm.is_admin') || $is_league_manager || $is_coordinator, $this->request->params['controller'], $this->request->params['action']));
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
