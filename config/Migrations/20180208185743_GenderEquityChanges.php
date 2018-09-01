<?php
use Migrations\AbstractMigration;

class GenderEquityChanges extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('people')
			->changeColumn('gender', 'string', ['limit' => 32])
			->addColumn('gender_description', 'string', ['limit' => 128, 'null' => true])
			->addColumn('roster_designation', 'string', ['limit' => 6])
			->update();

		$this->table('events')
			->renameColumn('cap_female', 'women_cap')
			->renameColumn('cap_male', 'open_cap')
			->update();

		$this->table('divisions')
			->renameColumn('ratio', 'ratio_rule')
			->update();

		$this->execute("UPDATE people SET roster_designation = 'Woman' WHERE gender = 'Female'");
		$this->execute("UPDATE people SET roster_designation = 'Open' WHERE gender = 'Male'");
		$this->execute("UPDATE people SET gender = 'Woman' WHERE gender = 'Female'");
		$this->execute("UPDATE people SET gender = 'Man' WHERE gender = 'Male'");
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('people')
			->changeColumn('gender', 'string', ['limit' => 6])
			->removeColumn('gender_description')
			->removeColumn('roster_designation')
			->update();

		$this->table('events')
			->renameColumn('women_cap', 'cap_female')
			->renameColumn('open_cap', 'cap_male')
			->update();

		$this->table('divisions')
			->renameColumn('ratio_rule', 'ratio')
			->update();

		$this->execute("UPDATE people SET gender = 'Female' WHERE gender = 'Woman'");
		$this->execute("UPDATE people SET gender = 'Male' WHERE gender = 'Man'");
	}
}
