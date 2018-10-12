<?php
use App\Controller\AppController;
use Cake\Core\Configure;

if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = [];

$unpaid = in_array($registration->payment, Configure::read('registration_unpaid')) && $registration->total_amount - $registration->total_payment > 0;
$unaccounted = $registration->payment == 'Paid' && $registration->total_payment != $registration->total_amount;

if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'view') {
		$links[] = $this->Html->iconLink("view_$size.png",
			['controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id],
			['alt' => __('View'), 'title' => __('View Registration')]
		);
	}
	if (($unpaid || $unaccounted) && $registration->payment != 'Waiting') {
		if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'add_payment') {
			$links[] = $this->Html->link(__('Add Payment'), ['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registration->id]);
		}
	}
	if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'edit') {
		$links[] = $this->Html->iconLink("edit_$size.png",
			['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registration->id, 'return' => AppController::_return()],
			['alt' => __('Edit'), 'title' => __('Edit Registration')]
		);
	}
}
if (in_array($registration->payment, Configure::read('registration_none_paid')) || $registration->total_amount == 0) {
	if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager')) {
		if ($this->request->getParam('controller') != 'Registrations' || $this->request->getParam('action') != 'edit') {
			$links[] = $this->Html->iconLink("edit_$size.png",
				['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registration->id, 'return' => AppController::_return()],
				['alt' => __('Edit'), 'title' => __('Edit Registration')]
			);
		}
	}
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
