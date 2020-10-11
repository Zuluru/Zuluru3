<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use ChasePayment\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('Chase', $globalListeners)) {
	$globalListeners['Chase'] = new Listener();
	EventManager::instance()->on($globalListeners['Chase']);
	Configure::write('App.globalListeners', $globalListeners);
}
