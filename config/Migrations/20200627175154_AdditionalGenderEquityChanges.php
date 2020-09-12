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

		\Cake\ORM\TableRegistry::getTableLocator()->get('Settings')
			->deleteAll(['category' => 'offerings']);
	}

}
