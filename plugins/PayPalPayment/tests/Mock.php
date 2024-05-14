<?php
namespace PayPalPayment\Test;

use App\Model\Entity\Registration;
use Cake\I18n\FrozenTime;

abstract class Mock {

	/**
	 * @param \PHPUnit\Framework\TestCase $test
	 * @return \PayPalPayment\Http\API
	 * @throws \ReflectionException
	 */
	public static function setup(\PHPUnit\Framework\TestCase $test, Registration $registration) {
		$method = (new \ReflectionClass($test))->getMethod('getMockBuilder');
		$method->setAccessible(true);
		$api = $method->invoke($test, 'PayPalPayment\Http\API')
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMethods(['SetExpressCheckout', 'GetExpressCheckoutDetails', 'DoExpressCheckoutPayment'])
			->getMock();

		$api->method('SetExpressCheckout')
			->will($test->returnValue([
				'TOKEN' => 'testing',
			]));

		$api->method('GetExpressCheckoutDetails')
			->will($test->returnValue([
				'PAYERID' => $registration->person_id,
				'TOKEN' => 'testing',
				'PAYMENTREQUEST_0_AMT' => 1.50, // There are already payments totalling 10 of 11.50
				'PAYMENTREQUEST_0_CURRENCYCODE' => 'CAD',
				'PAYMENTREQUEST_0_INVNUM' => $registration->id,
				'FIRSTNAME' => 'Crystal',
				'LASTNAME' => 'Captain',
				'PAYMENTREQUEST_0_CUSTOM' => $registration->person_id . ':' . $registration->id,
			]));

		$api->method('DoExpressCheckoutPayment')
			->will($test->returnValue([
				'PAYMENTINFO_0_ERRORCODE' => 0,
				'PAYMENTINFO_0_TRANSACTIONID' => '1234567890',
				'PAYMENTINFO_0_TRANSACTIONTYPE' => 'expresscheckout',
				'PAYMENTINFO_0_PAYMENTTYPE' => 'instant',
				'TIMESTAMP' => FrozenTime::now()->format('Y-m-d\TH:i:s\Z'),
			]));

		return $api;
	}

}
