<?php
/**
 * @var \App\Model\Entity\Event $event
 * @var string $format
 * @var string $size
 */

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = new ArrayObject();
$more = new ArrayObject();

if ($this->getRequest()->getParam('controller') != 'Events' || $this->getRequest()->getParam('action') != 'view') {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Events', 'action' => 'view', 'event' => $event->id],
		['alt' => __('View'), 'title' => __('View')]);
}

if (Configure::read('registration.register_now')) {
	if ($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'register') {
		$links[] = $this->Html->link(__('Register Now!'),
			['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id]);
	}
}

if (!empty($event->division_id)) {
	$links[] = $this->element('Divisions/block', ['division' => $event->division, 'link_text' => __('View Division')]);
}

if ($this->getRequest()->getParam('controller') != 'Events' || $this->getRequest()->getParam('action') != 'index') {
	$more[__('List Events')] = [
		'url' => ['controller' => 'Events', 'action' => 'index'],
	];
}

if ($this->Authorize->can('edit', $event)) {
	if ($this->getRequest()->getParam('controller') != 'Events' || $this->getRequest()->getParam('action') != 'edit') {
		$links[] = $this->Html->iconLink("edit_$size.png",
			['controller' => 'Events', 'action' => 'edit', 'event' => $event->id, 'return' => AppController::_return()],
			['alt' => __('Edit'), 'title' => __('Edit')]);
	}

	if ($this->getRequest()->getParam('controller') != 'Events' || $this->getRequest()->getParam('action') != 'connections') {
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
	if ($this->getRequest()->getParam('controller') != 'Events') {
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
	if ($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'summary') {
		$more[__('Registration Summary')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'summary', 'event' => $event->id],
		];
	}

	if ($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'full_list') {
		$more[__('Detailed Registration List')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'full_list', 'event' => $event->id],
		];
	}

	$more[__('Download Registration List')] = [
		'url' => ['controller' => 'Registrations', 'action' => 'full_list', 'event' => $event->id, '_ext' => 'csv'],
	];
}

if ($this->Authorize->can('waiting', $event)) {
	if ($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'summary') {
		$more[__('Waiting List')] = [
			'url' => ['controller' => 'Registrations', 'action' => 'waiting', 'event' => $event->id],
		];
	}
}

if ($this->Authorize->can('refund', $event)) {
	if ($this->getRequest()->getParam('controller') != 'Events' || $this->getRequest()->getParam('action') != 'refund') {
		$more[__('Bulk Refunds')] = [
			'url' => ['controller' => 'Events', 'action' => 'refund', 'event' => $event->id],
		];
	}
}

$plugin_event = new Event('Plugin.actions.event.links', $this, [$links, $more, $this->Authorize, $this->Html, $event]);
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
	$links[] = $this->Jquery->moreWidget(['type' => "event_actions_{$event->id}"], $more->getArrayCopy());
}

if ($format == 'links') {
	echo implode("\n", $links->getArrayCopy());
} else {
	echo $this->Html->nestedList($links->getArrayCopy(), ['class' => 'nav nav-pills']);
}
