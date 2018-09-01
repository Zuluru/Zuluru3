<?php
use Migrations\AbstractMigration;

class AddReports extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('reports')
			->addColumn('report', 'string', ['limit' => 32, 'null' => false])
			->addColumn('person_id', 'integer', ['null' => false])
			->addColumn('params', 'text', ['null' => false])
			->addColumn('failures', 'integer', ['null' => false, 'default' => 0])
			->create();
	}
}
