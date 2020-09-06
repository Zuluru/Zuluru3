<?php
namespace StripePayment\Test\TestCase\Controller;

use App\Test\TestCase\Controller\ControllerTestCase;
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
			$this->_controller->api = Mock::setup($this);
		}
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
