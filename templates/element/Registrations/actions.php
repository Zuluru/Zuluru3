<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var string $format
 * @var string $size
 */

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

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'view') &&
	$this->Authorize->can('view', $registration)
) {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]],
		['alt' => __('View'), 'title' => __('View Registration')]
	);
}

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'add_payment') &&
	$this->Authorize->can('add_payment', $registration)
) {
	$links[] = $this->Html->link(__('Add Payment'),
		['controller' => 'Registrations', 'action' => 'add_payment', '?' => ['registration' => $registration->id]],
		['class' => $this->Bootstrap->navPillLinkClasses()]
	);
}

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'edit') &&
	$this->Authorize->can('edit', $registration)
) {
	$links[] = $this->Html->iconLink("edit_$size.png",
		['controller' => 'Registrations', 'action' => 'edit', '?' => ['registration' => $registration->id, 'return' => AppController::_return()]],
		['alt' => __('Edit'), 'title' => __('Edit Registration')]
	);
}

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'invoice') &&
	$this->Authorize->can('invoice', $registration)
) {
	$links[] = $this->Html->link(__('View Invoice'),
		['controller' => 'Registrations', 'action' => 'invoice','?' => ['registration' => $registration->id]],
		['class' => $this->Bootstrap->navPillLinkClasses()]
	);
}

if ($this->Authorize->can('unregister', $registration)) {
	$links[] = $this->Html->link(__('Unregister'),
		['controller' => 'Registrations', 'action' => 'unregister', '?' => ['registration' => $registration->id, 'return' => AppController::_return()]],
		['confirm' => __('Are you sure you want to delete this registration?'), 'class' => $this->Bootstrap->navPillLinkClasses()]
	);
}

$plugin_event = new Event('Plugin.actions.registration.links', $this, [$links, $more, $this->Authorize, $this->Html, $registration]);
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
	$links[] = $this->Jquery->moreWidget(['type' => "registration_actions_{$registration->id}"], $more->getArrayCopy());
}

if ($format == 'links') {
	echo implode("\n", $links->getArrayCopy());
} else {
	echo $this->Bootstrap->navPills($links->getArrayCopy());
}
