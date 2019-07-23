<?php
use Migrations\AbstractMigration;

class UpdateGenderRatio extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('score_entries')
			->changeColumn('gender_ratio', 'string', [
				'length' => 32,
				'default' => null,
				'null' => true,
			])
			->update();
		$this->execute('UPDATE score_entries SET gender_ratio = NULL WHERE gender_ratio = \'\' OR gender_ratio like \'%W%\'');
		$this->table('score_entries')
			->changeColumn('gender_ratio', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->renameColumn('gender_ratio', 'women_present')
			->update();

		$this->execute('UPDATE settings SET name = \'women_present\' WHERE name = \'gender_ratio\'');
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('score_entries')
			->renameColumn('women_present', 'gender_ratio')
			->changeColumn('gender_ratio', 'string', ['length' => 32])
			->update();

		$this->execute('UPDATE settings SET name = \'gender_ratio\' WHERE name = \'women_present\'');
	}

}
