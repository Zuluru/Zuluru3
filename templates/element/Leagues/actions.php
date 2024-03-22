<?php
/**
 * @var \App\Model\Entity\League $league
 * @var string $format
 * @var string[] $division_actions
 * @var string[] $division_more
 * @var int $size
 * @var bool $collapse
 * @var bool $return
 */

use App\Controller\AppController;
use App\Controller\LeaguesController;
use Cake\Event\Event;

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
	$more = new ArrayObject();
}

$links = new ArrayObject();

if (!isset($tournaments)) {
	$tournaments = false;
}
$controller = ($tournaments ? 'Tournaments' : 'Leagues');
$model = ($tournaments ? 'tournament' : 'league');

if ($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => $controller, 'action' => 'view', '?' => [$model => $league->id]],
		['alt' => __('Details'), 'title' => $tournaments ? __('View Tournament Details') : __('View League Details')]);
}

$schedule_types = array_unique(collection($league->divisions)->extract('schedule_type')->toList());
if (!empty($schedule_types) && ($schedule_types[0] != 'none' || count($schedule_types) > 1)) {
	if ($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'schedule') {
		$links[] = $this->Html->iconLink("schedule_$size.png",
			['controller' => $controller, 'action' => 'schedule', '?' => [$model => $league->id]],
			['alt' => __('Schedule'), 'title' => __('Schedule')]);
	}
	if ($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'standings') {
		$links[] = $this->Html->iconLink("standings_$size.png",
			['controller' => $controller, 'action' => 'standings', '?' => [$model => $league->id]],
			['alt' => __('Standings'), 'title' => __('Standings')]);
	}
}

if ($this->Authorize->can('edit', $league)) {
	if ($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'edit') {
		$more[$tournaments ? __('Edit Tournament') : __('Edit League')] = [
			'url' => ['controller' => $controller, 'action' => 'edit', $model => $league->id, 'return' => AppController::_return()],
		];
	}

	if (($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'add') &&
		$this->Authorize->can('add', LeaguesController::class)
	) {
		$more[$tournaments ? __('Clone Tournament') : __('Clone League')] = [
			'url' => ['controller' => $controller, 'action' => 'add', $model => $league->id, 'return' => $return],
		];
	}

	if ($this->getRequest()->getParam('controller') != 'Divisions' || $this->getRequest()->getParam('action') != 'add') {
		$more[__('Add Division')] = [
			'url' => ['controller' => 'Divisions', 'action' => 'add', '?' => ['league' => $league->id, 'return' => $return]],
		];
	}
}

if ($this->Authorize->can('delete', $league)) {
	$url = ['controller' => $controller, 'action' => 'delete', $model => $league->id];
	if ($this->getRequest()->getParam('controller') != 'Leagues') {
		$url['return'] = AppController::_return();
	}
	$more[$tournaments ? __('Delete Tournament') : __('Delete League')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this league?'),
		'method' => 'post',
	];
}

if (($this->getRequest()->getParam('controller') != 'Leagues' || $this->getRequest()->getParam('action') != 'participation') &&
	$this->Authorize->can('participation', $league)
) {
	$more[__('Participation Report')] = [
		'url' => ['controller' => $controller, 'action' => 'participation', $model => $league->id]
	];
}

$plugin_event = new Event('Plugin.actions.league.links', $this, [$links, $more, $this->Authorize, $this->Html, $league]);
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
	if ($more->count() != 0) {
		$links[] = $this->Jquery->moreWidget(['type' => "league_actions_{$league->id}"], $more->getArrayCopy());
	}

	if ($format == 'links') {
		echo implode("\n", $links->getArrayCopy());
	} else {
		echo $this->Html->nestedList($links->getArrayCopy(), ['class' => 'nav nav-pills']);
	}
}
