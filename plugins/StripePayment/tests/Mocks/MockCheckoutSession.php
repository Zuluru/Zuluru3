<?php

namespace StripePayment\Test\Mocks;

class MockCheckoutSession {

	public $id = 12345;

	public function getLastResponse() {
		$response =  new \stdClass();
		$response->code = 200;

		return $response;
	}
}