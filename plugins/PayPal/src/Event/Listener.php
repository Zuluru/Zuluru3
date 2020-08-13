<?php
/**
 * Implementation of PayPal event listeners.
 */

namespace PayPal\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use PayPal\Http\API;

class Listener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['PayPal.checkout'] = ['api' => new API()];
	}

}
