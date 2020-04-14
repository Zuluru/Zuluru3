<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Questionnaire;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Questionnaire Test Case
 */
class QuestionnaireTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Questionnaire
	 */
	public $Questionnaire;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Questionnaire = new Questionnaire();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Questionnaire);

		parent::tearDown();
	}

	/**
	 * Test addResponseValidation method
	 *
	 * @return void
	 */
	public function testAddResponseValidation() {
		$this->markTestIncomplete('Not implemented yet. Pretty complex and I\'m not sure how this is used in the app yet.');
	}

}
