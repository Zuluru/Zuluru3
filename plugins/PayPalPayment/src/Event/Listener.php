<?php
/**
 * Implementation of PayPal event listeners.
 */

namespace PayPalPayment\Event;

use App\Controller\RegistrationsController;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use PayPalPayment\Http\API;

class Listener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['PayPalPayment.checkout'] = ['api' => new API(RegistrationsController::isTest())];
	}

}
