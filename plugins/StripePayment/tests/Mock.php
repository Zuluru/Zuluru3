<?php
namespace StripePayment\Test;

use StripePayment\Test\Mocks\MockCheckoutSession;

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
			->setMethods(['checkoutSessionCreate'])
			->getMock();

		$api->method('checkoutSessionCreate')
			->will($test->returnValue(new MockCheckoutSession()));

		return $api;
	}

}
