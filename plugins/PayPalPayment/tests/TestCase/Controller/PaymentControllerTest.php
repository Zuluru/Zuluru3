<?php
namespace PayPalPayment\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\ORM\TableRegistry;
use PayPalPayment\Test\Mock;

/**
 * PayPalPayment\Controller\PaymentController Test Case
 *
 * @property \PayPalPayment\Controller\PaymentController $_controller
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

	];

	/**
	 * Set up the mock API object to avoid talking to the PayPal servers
	 */
	public function controllerSpy($event, $controller = null) {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->api = Mock::setup($this);

		}
	}

	/**
	 * Test index method as a logged in user
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		// PayPal sends parameters in the URL.
		$this->assertGetAsAccessOk(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index', 'token' => 'TESTING'], PERSON_ID_CAPTAIN);

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
		$this->assertGetAnonymousAccessDenied(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index', 'token' => 'TESTING']);
	}

}
