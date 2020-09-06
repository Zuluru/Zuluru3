<?php
namespace StripePayment\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use StripePayment\Test\Mock;

/**
 * StripePayment\Controller\PaymentController Test Case
 *
 * @property \StripePayment\Controller\PaymentController $_controller
 */
class PaymentControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
			'app.Questions',
				'app.Answers',
			'app.Questionnaires',
				'app.QuestionnairesQuestions',
			'app.Events',
				'app.Prices',
					'app.Registrations',
						'app.Payments',
							'app.RegistrationAudits',
						'app.Responses',
				'app.Preregistrations',
			'app.Settings',
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Set up the mock API object to avoid talking to the Stripe servers
	 */
	public function controllerSpy($event, $controller = null) {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			// If we want to test different scenarios, we'll need to:
			// - write some config into $this before the post
			// - pass that config to the setup function
			// - use those details to build the return values
			$this->_controller->api = Mock::setup($this);
		}
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// Stripe posts data as an event.
		$payload = json_encode([
			'livemode' => false,
			'type' => 'checkout.session.completed',
			'data' => [
				'object' => [
					// The "session" used in the API
					'id' => 123,
					'client_reference_id' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
					'payment_intent' => [
						'object' => 'payment_intent',
						'id' => 234,
					],
				],
			],
		]);
		$time = FrozenTime::now()->getTimestamp();
		$signature = hash_hmac('sha256', "{$time}.{$payload}", Configure::read('payment.stripe_test_webhook_signing'));
		$_SERVER['HTTP_STRIPE_SIGNATURE'] = "t={$time},v1={$signature}";
		$this->assertPostAnonymousAccessOk(['plugin' => 'StripePayment', 'controller' => 'Payment', 'action' => 'index'], $payload);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(3, count($registration->payments));
	}

}
