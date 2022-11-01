<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * I18nFixture
 *
 */
class I18nFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'i18n'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'locale' => 'fr',
				'model' => 'Events',
				'foreign_key' => 1, // TODO: EVENT_ID_MEMBERSHIP, but this breaks unrelated tests
				'field' => 'name',
				'content' => 'Adhésion',
			],
		];

		parent::init();
	}

}
