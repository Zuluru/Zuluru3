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
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.groups',
				'app.groups_people',
			'app.settings',
		'app.i18n',
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
