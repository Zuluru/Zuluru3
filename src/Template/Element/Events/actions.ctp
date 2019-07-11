<?php
use App\Controller\AppController;
use Cake\Core\Configure;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = $more = [];

if ($this->request->getParam('controller') != 'Events' || $this->request->getParam('action') != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Events', 'action' => 'view', 'event' => $event->id],
		['alt' => __('View'), 'title' => __('View')]);
}

if (Configure::read('registration.register_now')) {
	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'register') {
		$links[] = $this->Html->link(__('Register Now!'),
			['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id]);
	}
}

if (!empty($event->division_id)) {
	$links[] = $this->element('Divisions/block', ['division' => $event->division, 'link_text' => __('View Division')]);
}

if ($this->request->getParam('controller') != 'Events' || $this->request->getParam('action') != 'index') {
	$more[__('List Events')] = [
		'url' => ['controller' => 'Events', 'action' => 'index'],
	];
}

if ($this->Authorize->can('edit', $event)) {
	if ($this->request->getParam('controller') != 'Events' || $this->request->getParam('action') != 'edit') {
		$links[] = $this->Html->iconLink("edit_$size.png",
			['controller' => 'Events', 'action' => 'edit', 'event' => $event->id, 'return' => AppController::_return()],
			['alt' => __('Edit'), 'title' => __('Edit')]);
	}

	if ($this->request->getParam('controller') != 'Events' || $this->request->getParam('action') != 'connections') {
		$more[__('Manage Connections')] = [
			'url' => ['controller' => 'Events', 'action' => 'connections', 'event' => $event->id],
		];
	}

	$more[__('Edit Questionnaire')] = [
		'url' => ['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' => $event->questionnaire_id, 'return' => AppController::_return()],
	];

	$more[__('Clone Event')] = [
		'url' => ['controller' => 'Events', 'action' => 'add', 'event' => $event->id, 'return' => AppController::_return()],
	];

	$url = ['controller' => 'Events', 'action' => 'delete', 'event' => $event->id];
	if ($this->request->getParam('controller') != 'Events') {
		$url['return'] = AppController::_return();
	}
	$more[__('Delete')] = [
		'url' => $url,
		'confirm' => __('Are you sure you want to delete this event?'),
		'method' => 'post',
	];

	$more[__('Add Preregistration')] = [
		'url' => ['controller' => 'Preregistrations', 'action' => 'add', 'event' => $event->id],
	];

	$more[__('List Preregistrations')] = [
		'url' => ['controller' => 'Preregistrations', 'action' => 'index', 'event' => $event->id],
	];
}

if ($this->Authorize->can('summary', $event)) {
	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'summary') {
		$more[__('Registration Summary')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'summary', 'event' => $event->id],
		];
	}

	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'full_list') {
		$more[__('Detailed Registration List')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'full_list', 'event' => $event->id],
		];
	}

	$more[__('Download Registration List')] = [
		'url' => ['controller' => 'Registrations', 'action' => 'full_list', 'event' => $event->id, '_ext' => 'csv'],
	];
}

if ($this->Authorize->can('waiting', $event)) {
	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'summary') {
		$more[__('Waiting List')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'waiting', 'event' => $event->id],
		];
	}
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$more = array_merge($more, $extra);
	} else {
		$more[] = $extra;
	}
}

$links[] = $this->Jquery->moreWidget(['type' => "event_actions_{$event->id}"], $more);
if ($format == 'links') {
	echo implode("\n", $links);
} else {
	echo $this->Html->nestedList($links, ['class' => 'nav nav-pills']);
}
