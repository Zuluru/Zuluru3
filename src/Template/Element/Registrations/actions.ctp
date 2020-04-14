<?php
use App\Controller\AppController;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = [];

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'view') &&
	$this->Authorize->can('view', $registration)
) {
	$links[] = $this->Html->iconLink("view_$size.png",
		['controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id],
		['alt' => __('View'), 'title' => __('View Registration')]
	);
}

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'add_payment') &&
	$this->Authorize->can('add_payment', $registration)
) {
	$links[] = $this->Html->link(__('Add Payment'), ['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registration->id]);
}

if (($this->getRequest()->getParam('controller') != 'Registrations' || $this->getRequest()->getParam('action') != 'edit') &&
	$this->Authorize->can('edit', $registration)
) {
	$links[] = $this->Html->iconLink("edit_$size.png",
		['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registration->id, 'return' => AppController::_return()],
		['alt' => __('Edit'), 'title' => __('Edit Registration')]
	);
}

if ($this->Authorize->can('unregister', $registration)) {
	$links[] = $this->Html->link(__('Unregister'),
		['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id, 'return' => AppController::_return()],
		['confirm' => __('Are you sure you want to delete this registration?')]
	);
}

if ($format == 'links') {
	echo implode("\n", $links);
} else {
	echo $this->Html->nestedList($links, ['class' => 'nav nav-pills']);
}
