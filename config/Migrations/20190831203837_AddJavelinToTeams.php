<?php
use Migrations\AbstractMigration;

class AddJavelinToTeams extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('teams')
			->addColumn('use_javelin', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->update();
	}
}
