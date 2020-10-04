<?php
use Migrations\AbstractMigration;

class AdditionalGenderEquityChanges extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('people')
			->addColumn('legal_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
				'after' => 'first_name',
			])
			->addColumn('pronouns', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
				'after' => 'roster_designation',
			])
			->addColumn('personal_pronouns', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
				'after' => 'pronouns',
			])
			->update();

		// Default pronouns for women and men; no good default for anyone else, so set them as incomplete and let them choose
		$this->execute("UPDATE people SET pronouns = 'She, Her, Hers' WHERE gender = 'Woman'");
		$this->execute("UPDATE people SET pronouns = 'He, Him, His' WHERE gender = 'Man'");
		$this->execute("UPDATE people SET complete = 0 WHERE gender NOT IN ('Woman', 'Man')");

		$this->execute("UPDATE people SET roster_designation = 'Womxn' WHERE roster_designation = 'Woman'");
		$this->execute("UPDATE people SET shirt_size = 'Womxns XSmall' WHERE roster_designation = 'Womens XSmall'");
		$this->execute("UPDATE people SET shirt_size = 'Womxns Small' WHERE roster_designation = 'Womens Small'");
		$this->execute("UPDATE people SET shirt_size = 'Womxns Medium' WHERE roster_designation = 'Womens Medium'");
		$this->execute("UPDATE people SET shirt_size = 'Womxns Large' WHERE roster_designation = 'Womens Large'");
		$this->execute("UPDATE people SET shirt_size = 'Womxns XLarge' WHERE roster_designation = 'Womens XLarge'");

		$settings = \Cake\ORM\TableRegistry::getTableLocator()->get('Settings');
		$settings->saveMany($settings->newEntities([
			[
				'category' => 'offerings',
				'name' => 'modes',
				'value' => 'Both',
			],
			[
				'category' => 'offerings',
				'name' => 'ages',
				'value' => 'Both',
			],
			[
				'category' => 'offerings',
				'name' => 'genders',
				'value' => 'Co-ed',
			],
		]));
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('people')
			->removeColumn('legal_name')
			->removeColumn('pronouns')
			->removeColumn('personal_pronouns')
			->update();

		$this->execute("UPDATE people SET roster_designation = 'Woman' WHERE roster_designation = 'Womxn'");
		$this->execute("UPDATE people SET shirt_size = 'Womens XSmall' WHERE roster_designation = 'Womxns XSmall'");
		$this->execute("UPDATE people SET shirt_size = 'Womens Small' WHERE roster_designation = 'Womxns Small'");
		$this->execute("UPDATE people SET shirt_size = 'Womens Medium' WHERE roster_designation = 'Womxns Medium'");
		$this->execute("UPDATE people SET shirt_size = 'Womens Large' WHERE roster_designation = 'Womxns Large'");
		$this->execute("UPDATE people SET shirt_size = 'Womens XLarge' WHERE roster_designation = 'Womxns XLarge'");

		\Cake\ORM\TableRegistry::getTableLocator()->get('Settings')
			->deleteAll(['category' => 'offerings']);
	}

}
