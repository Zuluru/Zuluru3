<?php
namespace StripePayment\Test\TestCase\Controller;

use App\Model\Entity\Registration;
use App\Test\Factory\PluginFactory;
use App\Test\Scenario\DiverseRegistrationsScenario;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\TestCase\Controller\ControllerTestCase;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use StripePayment\Test\Mock;

/**
 * StripePayment\Controller\PaymentController Test Case
 *
 * @property \StripePayment\Controller\PaymentController $_controller
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
	 * Set up the mock API object to avoid talking to the Stripe servers
	 */
	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void {
		parent::controllerSpy($event, $controller);

		if (isset($this->_controller)) {
			// If we want to test different scenarios, we'll need to:
			// - write some config into $this before the post
			// - pass that config to the setup function
			// - use those details to build the return values
			$this->_controller->api = Mock::setup($this, $this->membership)
				->setTest(true);
		}
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		PluginFactory::make(['name' => 'Stripe', 'load_name' => 'StripePayment', 'path' => 'plugins/StripePayment'])
			->persist();
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);
		$this->membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		// Stripe posts data as an event.
		$payload = json_encode([
			'livemode' => false,
			'type' => 'checkout.session.completed',
			'data' => [
				'object' => [
					// The "session" used in the API
					'id' => 123,
					'client_reference_id' => $this->membership->id,
					'payment_intent' => [
						'object' => 'payment_intent',
						'id' => 234,
					],
				],
			],
		]);
		$time = FrozenTime::now()->getTimestamp();
		// @todo: Any way to get these settings from the config instead?
		$signature = hash_hmac('sha256', "{$time}.{$payload}", 'ABCDE');
		$_SERVER['HTTP_STRIPE_SIGNATURE'] = "t={$time},v1={$signature}";
		$this->assertPostAnonymousAccessOk(['plugin' => 'StripePayment', 'controller' => 'Payment', 'action' => 'index'], $payload);

		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($this->membership->id, [
			'contain' => ['Payments' => ['RegistrationAudits']]
		]);
		$this->assertResponseContains('OK');
		$this->assertResponseNotContains('Your payment was declined');
		$this->assertEquals('Paid', $registration->payment);
		$this->assertCount(1, $registration->payments);
		$this->assertEquals(11.50, $registration->payments[0]->registration_audit->charge_total);
	}

}
