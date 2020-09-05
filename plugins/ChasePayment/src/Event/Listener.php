<?php
/**
 * Implementation of Chase event listeners.
 */

namespace ChasePayment\Event;

use App\Controller\RegistrationsController;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use ChasePayment\Http\API;

class Listener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['ChasePayment.checkout'] = ['api' => new API(RegistrationsController::isTest())];
	}

}
