<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class CreateMembershipTypes extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('membership_types')
			->addColumn('name', 'string', ['limit' => 40])
			->addColumn('description', 'string', ['limit' => 40])
			->addColumn('active', 'boolean', ['null' => true, 'default' => true])
			->addColumn('priority', 'integer', ['null' => false, 'default' => null])
			->addColumn('report_as', 'string', ['limit' => 32])
			->addColumn('badge', 'string', ['limit' => 32])
			->create();

		$migrations = new Migrations();
		$migrations->seed(['seed' => 'MembershipTypesSeed']);
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('membership_types')
			->drop();
	}
}
