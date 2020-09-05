<?php

namespace StripePayment\Test\Mocks;

class MockCheckoutSession {

	public function getLastResponse() {
		$response =  new \stdClass();
		$response->code = 200;
		$response->id = 12345;

		return $response;
	}
}