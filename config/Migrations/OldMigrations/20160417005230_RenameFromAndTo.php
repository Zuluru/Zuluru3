<?php
use Migrations\AbstractMigration;

class RenameFromAndTo extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('newsletters')
			->renameColumn('from', 'from_email')
			->renameColumn('to', 'to_email')
			->save();
	}
}
