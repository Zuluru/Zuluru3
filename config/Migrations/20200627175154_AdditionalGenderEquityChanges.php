<?php
use Migrations\AbstractMigration;

class AdditionalGenderEquityChanges extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('people')
			->addColumn('legal_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
				'after' => 'first_name',
			])
			->update();
	}
}
