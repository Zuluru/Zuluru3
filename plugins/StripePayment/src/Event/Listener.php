<?php
/**
 * Implementation of Stripe event listeners.
 */

namespace StripePayment\Event;

use App\Controller\RegistrationsController;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use StripePayment\Http\API;

class Listener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['StripePayment.checkout'] = ['api' => new API(RegistrationsController::isTest())];
	}

}
