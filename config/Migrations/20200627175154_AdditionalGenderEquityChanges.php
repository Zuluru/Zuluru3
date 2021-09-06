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
			->addColumn('publish_gender', 'boolean', [
				'default' => false,
				'null' => false,
				'after' => 'gender_description',
			])
			->addColumn('pronouns', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
				'after' => 'roster_designation',
			])
			->addColumn('publish_pronouns', 'boolean', [
				'default' => false,
				'null' => false,
				'after' => 'pronouns',
			])
			->update();

		$this->execute("UPDATE people SET gender = '', complete = 0 WHERE gender = 'Trans'");
		$this->execute("UPDATE people SET gender = 'Prefer to specify' WHERE gender = 'Self-defined'");

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
			->removeColumn('publish_gender')
			->removeColumn('pronouns')
			->removeColumn('publish_pronouns')
			->update();

		$this->execute("UPDATE people SET gender = 'Self-defined' WHERE gender = 'Prefer to specify'");

		\Cake\ORM\TableRegistry::getTableLocator()->get('Settings')
			->deleteAll(['category' => 'offerings']);
	}

}
