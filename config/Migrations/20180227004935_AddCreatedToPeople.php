<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class AddCreatedToPeople extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('people')
			->addColumn('created', 'date', ['null' => true, 'after' => 'modified'])
			->update();

		// Set the created date to the last modified, as a first guess
		$this->execute('UPDATE people SET created = modified WHERE modified != "0000-00-00"');

		// Go through a series of join tables, updating with any earlier creation time we find
		foreach (['teams_people', 'notices_people', 'badges_people', 'people_people', 'waivers_people', 'registrations', 'uploads'] as $join) {
			$this->execute("UPDATE people p JOIN(SELECT person_id, MIN(created) AS created FROM $join GROUP BY person_id) j ON p.id = j.person_id SET p.created = j.created WHERE j.created != '0000-00-00' AND (p.created IS NULL OR p.created > j.created)");
		}
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('people')
			->removeColumn('created')
			->update();
	}
}
