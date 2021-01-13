<?php
use Migrations\AbstractMigration;

class DeleteSchemaSetting extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function up() {
		// This has never been used by Zuluru, and is fully obsoleted by Phinx migrations
		$this->execute('DELETE FROM settings WHERE name = \'_SchemaVersion\'');
	}
}
