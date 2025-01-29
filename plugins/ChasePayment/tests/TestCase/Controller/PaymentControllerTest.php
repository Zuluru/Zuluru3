<?php
namespace ChasePayment\Test\TestCase\Controller;

use App\Test\Factory\PluginFactory;
use App\Test\Scenario\DiverseRegistrationsScenario;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * ChasePayment\Controller\PaymentController Test Case
 *
 * @property \ChasePayment\Controller\PaymentController $_controller
 */
class PaymentControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.UserGroups',
		'app.Settings',
	];

	/**
	 * Set up the mock API object to avoid talking to the Chase servers
	 */
	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->api = $this->getMockBuilder('ChasePayment\Http\API')
				->disableOriginalConstructor()
				->disableOriginalClone()
				->disableArgumentCloning()
				->disallowMockingUnknownTypes()
				->setMethods(null)
				->getMock()
				->setTest(true);
		}
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// Chase posts parameters. Posts don't use CSRF or form security.
		// @todo: Any way to get these settings from the config instead?
		$login = 'ABC-ABCDE-123';
		$key = 'a1b2c3d4e5f6g7h8i9j0';
		$calculated_hash = md5("{$key}{$login}12345678907.00");

		PluginFactory::make(['name' => 'Chase Paymentech', 'load_name' => 'ChasePayment', 'path' => 'plugins/ChasePayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		$this->assertPostAnonymousAccessOk(['plugin' => 'ChasePayment', 'controller' => 'Payment', 'action' => 'index'], [
			'exact_ctr' => 'DATE/TIME: ' . FrozenTime::now()->format('d M y H:i:s'),
			'Reference_No' => 'R' . $membership->id,
			'Bank_Resp_Code' => '000',
			'Bank_Message' => 'APPROVED',
			'CardHoldersName' => 'Crystal Captain',
			'Expiry_Date' => FrozenTime::now()->addYears(1)->format('MMyy'),
			'Card_Number' => '############1234',
			'TransactionCardType' => 'VISA',
			'x_response_code' => 1,
			'x_trans_id' => '1234567890',
			'x_auth_code' => '12345A',
			'x_amount' => '7.00',
			'x_MD5_Hash' => $calculated_hash,
			'x_description' => $membership->id,
		]);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($membership->id, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertCount(1, $registration->payments);
	}

}
