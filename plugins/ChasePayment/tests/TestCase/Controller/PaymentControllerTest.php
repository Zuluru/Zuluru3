<?php
namespace ChasePayment\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * ChasePayment\Controller\PaymentController Test Case
 *
 * @property \ChasePayment\Controller\PaymentController $_controller
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
			$this->_controller->api = $this->getMockBuilder('ChasePayment\Http\API')
				->disableOriginalConstructor()
				->disableOriginalClone()
				->disableArgumentCloning()
				->disallowMockingUnknownTypes()
				->setMethods(null)
				->getMock();
		}
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// Chase posts parameters. Posts don't use CSRF or form security.
		$login = Configure::read('payment.chase_test_store');
		$key = Configure::read('payment.chase_test_response');
		$calculated_hash = md5("{$key}{$login}12345678907.00");

		$this->assertPostAnonymousAccessOk(['plugin' => 'ChasePayment', 'controller' => 'Payment', 'action' => 'index'], [
			'exact_ctr' => 'DATE/TIME: ' . FrozenTime::now()->format('d M y H:i:s'),
			'Reference_No' => 'R00000000' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
			'Bank_Resp_Code' => '000',
			'Bank_Message' => 'APPROVED',
			'CardHoldersName' => 'Crystal Captain',
			'Expiry_Date' => FrozenTime::now()->addYear()->format('MMyy'),
			'Card_Number' => '############1234',
			'TransactionCardType' => 'VISA',
			'x_response_code' => 1,
			'x_trans_id' => '1234567890',
			'x_auth_code' => '12345A',
			'x_amount' => '7.00',
			'x_MD5_Hash' => $calculated_hash,
			'x_description' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
		]);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(3, count($registration->payments));
	}

}
