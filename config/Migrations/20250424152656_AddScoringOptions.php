<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddScoringOptions extends AbstractMigration
{
	/**
	 * Up Method.
	 */
	public function up(): void
	{
		$this->execute('INSERT INTO settings (category, name, value) VALUES (\'scoring\', \'score_entry_by\', 1)');
		$this->execute('INSERT INTO settings (category, name, value) VALUES (\'scoring\', \'spirit_entry_by\', 1)');
	}

	/**
	 * Down Method.
	 */
	public function down(): void
	{
		$this->execute('DELETE FROM settings WHERE name IN (\'score_entry_by\', \'spirit_entry_by\')');
	}
}
