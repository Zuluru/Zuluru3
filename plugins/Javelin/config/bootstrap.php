<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Javelin\Event\Listener;

/**
 * Create and register all the required listener objects
 */
if (Configure::check('App.globalListeners')) {
	$globalListeners = Configure::read('App.globalListeners');
} else {
	$globalListeners = [];
}

if (!array_key_exists('Javelin', $globalListeners)) {
	$globalListeners['Javelin'] = new Listener();
	EventManager::instance()->on($globalListeners['Javelin']);
	Configure::write('App.globalListeners', $globalListeners);
}
