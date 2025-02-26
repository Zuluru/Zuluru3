<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class AddI18nTable extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		if (defined('PHPUNIT_TESTSUITE') && PHPUNIT_TESTSUITE) {
			return;
		}

		$this->table('i18n')
			->addColumn('locale', 'string', ['limit' => 6, 'null' => false, 'default' => null])
			->addColumn('model', 'string', ['limit' => 255, 'null' => false, 'default' => null])
			->addColumn('foreign_key', 'integer', ['null' => false, 'default' => null])
			->addColumn('field', 'string', ['limit' => 255, 'null' => false, 'default' => null])
			->addColumn('content', 'text', ['null' => true, 'default' => null])
			->addIndex(['locale', 'model', 'foreign_key', 'field'], ['name' => 'I18N_LOCALE_FIELD', 'unique' => true])
			->addIndex(['model', 'foreign_key', 'field'], ['name' => 'I18N_FIELD'])
			->create();

		$migrations = new Migrations();
		$migrations->seed(['seed' => 'I18nSeed']);
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('i18n')
			->drop();
	}
}
