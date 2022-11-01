<?php
use Migrations\AbstractMigration;

class AddGenderRatioToScoreEntries extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('score_entries')
			->addColumn('gender_ratio', 'string', ['limit' => 32])
			->update();
	}
}
