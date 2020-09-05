<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use StripePayment\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('Stripe', $globalListeners)) {
	$globalListeners['Stripe'] = new Listener();
	EventManager::instance()->on($globalListeners['Stripe']);
	Configure::write('App.globalListeners', $globalListeners);
}
