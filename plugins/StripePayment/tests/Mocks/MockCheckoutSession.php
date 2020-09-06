<?php

namespace StripePayment\Test\Mocks;

class MockCheckoutSession {

	public $id = 12345;

	public function getLastResponse() {
		return json_decode(json_encode(['code' => 200]));
	}

}
