<?php
/**
 * Implementation of Stripe event listeners.
 */

namespace StripePayment\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use StripePayment\Http\API;

class Listener implements EventListenerInterface {

	/**
	 * @var \StripePayment\Http\API
	 */
	public $api = null;

	public function implementedEvents(): array {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['StripePayment.checkout'] = ['listener' => $this];
	}

	public function getAPI($test) {
		if (!$this->api) {
			$this->api = new API($test);
		}

		return $this->api;
	}

}
