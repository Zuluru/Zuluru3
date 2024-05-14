<?php
namespace PayPalPayment\Test\TestCase\Controller;

use App\Model\Entity\Registration;
use App\Test\Factory\PluginFactory;
use App\Test\Scenario\DiverseRegistrationsScenario;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use PayPalPayment\Test\Mock;

/**
 * PayPalPayment\Controller\PaymentController Test Case
 *
 * @property \PayPalPayment\Controller\PaymentController $_controller
 */
class PaymentControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	private Registration $membership;

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
	 * Set up the mock API object to avoid talking to the PayPal servers
	 */
	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			$this->_controller->api = Mock::setup($this, $this->membership)
				->setTest(true);

		}
	}

	/**
	 * Test index method as a logged in user
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		PluginFactory::make(['name' => 'PayPal', 'load_name' => 'PayPalPayment', 'path' => 'plugins/PayPalPayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$this->membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		// PayPal sends parameters in the URL.
		$this->assertGetAsAccessOk(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index', '?' => ['token' => 'TESTING']], $player->id);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($this->membership->id, [
			'contain' => ['Payments']
		]);
		$this->assertResponseContains('Your Transaction has been Approved');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(1, count($registration->payments));
	}

	/**
	 * PayPal redirects the actual user to the payment page, so anonymous access is not allowed
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		PluginFactory::make(['name' => 'PayPal', 'load_name' => 'PayPalPayment', 'path' => 'plugins/PayPalPayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$this->membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		// PayPal sends parameters in the URL.
		$this->assertGetAnonymousAccessDenied(['plugin' => 'PayPalPayment', 'controller' => 'Payment', 'action' => 'index', '?' => ['token' => 'TESTING']]);
	}

}
