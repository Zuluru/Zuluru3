<?php
/**
 * Implementation of PayPal event listeners.
 */

namespace PayPalPayment\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use PayPalPayment\Http\API;

class Listener implements EventListenerInterface {

	/**
	 * @var \PayPalPayment\Http\API
	 */
	public $api = null;

	/**
	 * @return array|string[]
	 */
	public function implementedEvents() {
		return [
			// Listeners for events that collect elements to be displayed
			'Plugin.checkout' => 'checkout',
		];
	}

	/**
	 * @param Event $event
	 * @param \ArrayObject $elements
	 */
	public function checkout(Event $event, \ArrayObject $elements) {
		$elements['PayPalPayment.checkout'] = ['listener' => $this];
	}

	/**
	 * @param $test
	 * @return API
	 */
	public function getAPI($test) {
		if (!$this->api) {
			$this->api = new API($test);
		}

		return $this->api;
	}

}
