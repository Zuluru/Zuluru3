<?php
/**
 * Implementation of Stripe event listeners.
 */

namespace Stripe\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class Listener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements[] = 'Stripe.checkout';
	}

}
