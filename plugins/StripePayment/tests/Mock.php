<?php
namespace StripePayment\Test;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use StripePayment\Test\Mocks\MockCheckoutSession;
use StripePayment\Test\Mocks\MockPaymentIntent;

abstract class Mock {

	/**
	 * @param \PHPUnit_Framework_TestCase $test
	 * @return \StripePayment\Http\API
	 * @throws \ReflectionException
	 */
	public static function setup(\PHPUnit_Framework_TestCase $test) {
		$method = (new \ReflectionClass($test))->getMethod('getMockBuilder');
		$method->setAccessible(true);
		$api = $method->invoke($test, 'StripePayment\Http\API')
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMethods(['checkoutSessionCreate', 'paymentIntentsRetrieve', 'getRegistrationIds'])
			->getMock();

		$api->method('checkoutSessionCreate')
			->will($test->returnValue(new MockCheckoutSession()));

		$api->method('paymentIntentsRetrieve')
			// Easy way to convert an array into nested objects
			->will($test->returnValue(json_decode(json_encode([
				'status' => 'succeeded',
				'created' => FrozenTime::now()->getTimestamp(),
				'amount' => 7,
				'charges' => [
					'data' => [
						[
							'billing_details' => [
								'name' => 'Crystal Captain',
							],
							'payment_method_details' => [
								'card' => [
									'funding' => 'Approved',
									'exp_month' => 12,
									'exp_year' => FrozenDate::now()->year + 1,
									'last4' => 1234,
									'brand' => 'Visa',
								],
							],
							'outcome' => [
								'seller_message' => 'message',
							],
						],
					],
				],
			]))));

		$api->method('getRegistrationIds')
			->will($test->returnValue([REGISTRATION_ID_CAPTAIN_MEMBERSHIP]));

		return $api;
	}

}
