<?php
namespace ElavonPayment\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * ElavonPayment\Controller\PaymentController Test Case
 *
 * @property \ElavonPayment\Controller\PaymentController $_controller
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
	 * Set up the mock API object to avoid talking to the Elavon servers
	 */
	public function controllerSpy($event, $controller = null) {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->api = $this->getMockBuilder('ElavonPayment\Http\API')
				->disableOriginalConstructor()
				->disableOriginalClone()
				->disableArgumentCloning()
				->disallowMockingUnknownTypes()
				->setMethods(null)
				->getMock();
		}
	}

	/**
	 * Test index method as a logged in user
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		// Elavon sends parameters in the URL.
		$data = [
			'trnApproved' => '1',
			'trnId' => '123456',
			'authCode' => 'TEST',
			'messageId' => '1',
			'messageText' => 'Approved',
			'trnAmount' => '7.00',
			'trnOrderNumber' => 'R00000000' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
			'cardType' => 'VI',
			'trnDate' => FrozenTime::now()->format('n/j/y g:i:s A'),
			'trnType' => 'P',
			'paymentMethod' => 'CC',
			'ref1' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
			'ref2' => EVENT_ID_MEMBERSHIP,
			'ref3' => PERSON_ID_CAPTAIN,
		];
		$x = http_build_query($data);
		$data['hashValue'] = sha1(http_build_query($data) . '12345678-ABCD-EFGH-1234-12345678');

		$this->assertGetAsAccessOk(array_merge(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index'], $data), PERSON_ID_CAPTAIN);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(3, count($registration->payments));
	}

	/**
	 * Elavon redirects the actual user to the payment page, so anonymous access is not allowed
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// PayPal sends parameters in the URL.
		$this->assertGetAnonymousAccessDenied(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index']);
	}

}
