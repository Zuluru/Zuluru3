<?php
namespace StripePayment\Test;

use App\Model\Entity\Registration;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Stripe\PaymentIntent;
use StripePayment\Http\API;
use StripePayment\Test\Mocks\MockCheckoutSession;

abstract class Mock {

	/**
	 * @param \PHPUnit\Framework\TestCase $test
	 * @return \StripePayment\Http\API
	 * @throws \ReflectionException
	 */
	public static function setup(\PHPUnit\Framework\TestCase $test, Registration $registration): API {
		$method = (new \ReflectionClass($test))->getMethod('getMockBuilder');
		$method->setAccessible(true);
		$client = $method->invoke($test, 'StripePayment\Http\Client')
			->setConstructorArgs([true])
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMethods(['checkoutSessionCreate', 'paymentIntentsRetrieve', 'getRegistrationIds'])
			->getMock();

		$client->method('checkoutSessionCreate')
			->will($test->returnValue(new MockCheckoutSession()));

		$client->method('paymentIntentsRetrieve')
			// Easy way to convert an array into nested objects
			->will($test->returnValue(PaymentIntent::constructFrom([
				'status' => 'succeeded',
				'created' => FrozenTime::now()->getTimestamp(),
				'amount' => 1150,
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
			])));

		$client->method('getRegistrationIds')
			->will($test->returnValue([[$registration->id], []]));

		return (new API(true))->setClient($client);
	}
}
