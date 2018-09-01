<?php
use Migrations\AbstractMigration;

class RenamePreferenceFields extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('teams')
			->renameColumn('home_field', 'home_field_id')
			->renameColumn('region_preference', 'region_preference_id')
			->save();
	}
}
