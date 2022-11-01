<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class UpdateNotices extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->execute('TRUNCATE TABLE notices');

		$this->table('notices')
			->addColumn('sort', 'integer', ['null' => true, 'default' => null, 'after' => 'id'])
			->update();

		$migrations = new Migrations();
		$migrations->seed(['seed' => 'NoticesSeed']);
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('notices')
			->removeColumn('sort')
			->update();
	}
}
