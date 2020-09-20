<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use BamboraPayment\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('Bambora', $globalListeners)) {
	$globalListeners['Bambora'] = new Listener();
	EventManager::instance()->on($globalListeners['Bambora']);
	Configure::write('App.globalListeners', $globalListeners);
}
