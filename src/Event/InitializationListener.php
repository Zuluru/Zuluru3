<?php
namespace App\Event;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class InitializationListener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			'Controller.beforeRender' => 'beforeRender',
		];
	}

	public function beforeRender(Event $event) {
		// Set the theme, if any
		$theme = Configure::read('App.theme');
		if (!empty($theme)) {
			Plugin::load($theme);
			// Assumption here is that the subject is always a controller
			$event->subject()->viewBuilder()->theme($theme);
		}
	}

}
