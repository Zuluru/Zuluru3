<?php
namespace ElavonPayment\Test\TestCase\Controller;

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
 * ElavonPayment\Controller\PaymentController Test Case
 *
 * @property \ElavonPayment\Controller\PaymentController $_controller
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
	 * Set up the mock API object to avoid talking to the Elavon servers
	 */
	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->api = $this->getMockBuilder('ElavonPayment\Http\API')
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
	 * Test index method as a logged in user
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		PluginFactory::make(['name' => 'Elavon', 'load_name' => 'ElavonPayment', 'path' => 'plugins/ElavonPayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		// Elavon posts parameters. Posts don't use CSRF or form security.
		$data = [
			'ssl_txn_id' => '123456789-123A4567-B8C9-123D-4567-66AB6666ABCD',
			'ssl_approval_code' => '123456',
			'ssl_result' => '0',
			'ssl_result_message' => 'APPROVAL',
			'ssl_amount' => '7.00',
			'ssl_invoice_number' => 'R' . $membership->id,
			'ssl_card_short_description' => '',
			'ssl_transaction_type' => 'SALE',
			'ssl_card_number' => '45**********0123',
			'ssl_txn_time' => FrozenTime::now()->format('n/j/y g:i:s A'),
			'ssl_description' => $membership->id,
		];
		$data['hashValue'] = sha1(http_build_query($data) . '12345678-ABCD-EFGH-1234-12345678');

		$this->assertPostAsAccessOk(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index'], $player->id, $data);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($membership->id, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(1, count($registration->payments));
	}

	/**
	 * Elavon redirects the actual user to the payment page, so anonymous access is not allowed
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		PluginFactory::make(['name' => 'Elavon', 'load_name' => 'ElavonPayment', 'path' => 'plugins/ElavonPayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		// Elavon posts parameters. Posts don't use CSRF or form security.
		// @todo: Is this really the right thing? Does it really need to be a no-auth-required function?
		$this->assertPostAnonymousAccessRedirect(['plugin' => 'ElavonPayment', 'controller' => 'Payment', 'action' => 'index'], [], '/', 'Invalid data');
	}

}
