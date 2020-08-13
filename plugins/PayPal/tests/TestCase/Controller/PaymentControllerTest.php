<?php
namespace PayPal\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * PayPal\Controller\PaymentController Test Case
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
	 * Set up the mock API object to avoid talking to the PayPal servers
	 */
	public function controllerSpy($event, $controller = null) {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->_api = $this->createMock('PayPal\Http\API');

			$this->_controller->_api
				->method('GetExpressCheckoutDetails')
				->will($this->returnValue([
					'PAYERID' => PERSON_ID_CAPTAIN,
					'TOKEN' => 'testing',
					'PAYMENTREQUEST_0_AMT' => 1.50, // There are already payments totalling 10 of 11.50
					'PAYMENTREQUEST_0_CURRENCYCODE' => 'CAD',
					'PAYMENTREQUEST_0_INVNUM' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
					'FIRSTNAME' => 'Crystal',
					'LASTNAME' => 'Captain',
					'PAYMENTREQUEST_0_CUSTOM' => PERSON_ID_CAPTAIN . ':' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
				]));

			$this->_controller->_api
				->method('DoExpressCheckoutPayment')
				->will($this->returnValue([
					'PAYMENTINFO_0_ERRORCODE' => 0,
					'PAYMENTINFO_0_TRANSACTIONID' => '1234567890',
					'PAYMENTINFO_0_TRANSACTIONTYPE' => 'expresscheckout',
					'PAYMENTINFO_0_PAYMENTTYPE' => 'instant',
					'TIMESTAMP' => FrozenTime::now()->format('Y-m-d\TH:i:s\Z'),
				]));
		}
	}

	/**
	 * Test index method as a logged in user
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		// PayPal sends parameters in the URL.
		$this->assertGetAsAccessOk(['plugin' => 'PayPal', 'controller' => 'Payment', 'action' => 'index', 'token' => 'TESTING'], PERSON_ID_CAPTAIN);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(3, count($registration->payments));
	}

	/**
	 * PayPal redirects the actual user to the payment page, so anonymous access is not allowed
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// PayPal sends parameters in the URL.
		$this->assertGetAnonymousAccessDenied(['plugin' => 'PayPal', 'controller' => 'Payment', 'action' => 'index', 'token' => 'TESTING']);
	}

}
