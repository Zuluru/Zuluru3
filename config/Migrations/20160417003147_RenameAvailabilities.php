<?php
use Migrations\AbstractMigration;

class RenameAvailabilities extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('division_gameslot_availabilities')
			->rename('divisions_gameslots')
			->save();
	}
}
