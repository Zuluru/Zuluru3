<?php
namespace App\Event;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;

class InitializationListener implements EventListenerInterface {

	public function implementedEvents(): array {
		return [
			'Controller.beforeRender' => 'beforeRender',
		];
	}

	public function beforeRender(EventInterface $event): void {
		// Set the theme, if any
		$theme = Configure::read('App.theme');
		if (!empty($theme)) {
			// Assumption here is that the subject is always a controller
			$event->getSubject()->viewBuilder()->setTheme($theme);
		}
	}

}
