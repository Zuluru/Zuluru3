<?php
namespace App\Event;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Muffin\Footprint\Auth\FootprintAwareTrait;

class InitializationListener implements EventListenerInterface {

	use FootprintAwareTrait;

	public function implementedEvents() {
		return [
			'Controller.initialize' => 'beforeFilter',
			'Controller.beforeRender' => 'beforeRender',
		];
	}

	/**
	 * beforeFilter
	 *
	 * Application hook which runs prior to each controller action
	 *
	 * @param \Cake\Event\Event $event The beforeFilter event.
	 * @return void
	 */
	public function beforeFilter(Event $event) {
		Configure::load('options');

		// Test cases don't necessarily have a plugin property, but need this done anyway.
		if (!property_exists($event->subject(), 'plugin') || $event->subject()->plugin != 'Installer') {
			// Load configuration from database or cache
			$configuration_table = TableRegistry::get('Configuration');
			$configuration_table->loadSystem();
			if (Configure::read('feature.affiliates')) {
				$affiliates = AppController::_applicableAffiliateIDs();
				if (count($affiliates) == 1) {
					$configuration_table->loadAffiliate(current($affiliates));
				}
			}
		}

		Configure::load('sports');
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
