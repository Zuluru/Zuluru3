<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use PayPalPayment\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('PayPal', $globalListeners)) {
	$globalListeners['PayPal'] = new Listener();
	EventManager::instance()->on($globalListeners['PayPal']);
	Configure::write('App.globalListeners', $globalListeners);
}
