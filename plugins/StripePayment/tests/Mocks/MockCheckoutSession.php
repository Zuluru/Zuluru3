<?php
namespace StripePayment\Test\Mocks;

use Stripe\Checkout\Session;

class MockCheckoutSession {

	public $id = 12345;

	public function getLastResponse() {
		return Session::constructFrom(['code' => 200]);
	}
}
