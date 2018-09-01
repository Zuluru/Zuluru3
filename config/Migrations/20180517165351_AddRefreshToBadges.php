<?php
use Migrations\AbstractMigration;

class AddRefreshToBadges extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('badges')
			->addColumn('refresh_from', 'integer', ['null' => false, 'default' => 0])
			->update();
		$this->execute('UPDATE badges SET refresh_from = 1 WHERE category IN (\'game\', \'registration\', \'team\') AND handler != \'\'');
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('badges')
			->removeColumn('refresh_from')
			->update();
	}
}
