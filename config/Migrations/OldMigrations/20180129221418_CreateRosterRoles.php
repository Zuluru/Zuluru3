<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class CreateRosterRoles extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('roster_roles')
			->addColumn('name', 'string', ['limit' => 40])
			->addColumn('description', 'string', ['limit' => 40])
			->addColumn('active', 'boolean', ['null' => true, 'default' => true])
			->addColumn('is_player', 'boolean', ['null' => true, 'default' => null])
			->addColumn('is_extended_player', 'boolean', ['null' => true, 'default' => null])
			->addColumn('is_regular', 'boolean', ['null' => true, 'default' => null])
			->addColumn('is_privileged', 'boolean', ['null' => true, 'default' => null])
			->addColumn('is_required', 'boolean', ['null' => true, 'default' => null])
			->create();

		$migrations = new Migrations();
		$migrations->seed(['seed' => 'RosterRolesSeed']);
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('roster_roles')
			->drop();
	}
}
