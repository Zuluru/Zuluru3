<?php
use Migrations\AbstractMigration;

class RenameGroups extends AbstractMigration {
	/**
	 * Up Method.
	 */
	public function change(): void {
		// We rename the table only if it exists. It will for old installs being updated. It will not for new installs,
		// where the install migration has already created it with the new name, for MySQL 8 compatibility.
		if ($this->table('groups')->exists()) {
			$this->table('groups')
				->rename('user_groups')
				->update();
		}
	}
}
