<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class ChangeDaysIds extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->execute("UPDATE days SET id = id - 1");
		$this->execute("UPDATE days SET id = 7 WHERE id = 0");
		$this->execute("UPDATE divisions_days SET day_id = day_id - 1");
		$this->execute("UPDATE divisions_days SET day_id = 7 WHERE day_id = 0");
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->execute("UPDATE days SET id = id + 1 ORDER BY id DESC");
		$this->execute("UPDATE days SET id = 1 WHERE id = 8");
		$this->execute("UPDATE divisions_days SET day_id = day_id + 1");
		$this->execute("UPDATE divisions_days SET day_id = 1 WHERE day_id = 8");
	}
}
