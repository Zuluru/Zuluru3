<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use ElavonPayment\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('Elavon', $globalListeners)) {
	$globalListeners['Elavon'] = new Listener();
	EventManager::instance()->on($globalListeners['Elavon']);
	Configure::write('App.globalListeners', $globalListeners);
}
