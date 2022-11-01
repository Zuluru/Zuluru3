<?php
use Migrations\AbstractMigration;

class AddSecondaryKeys extends AbstractMigration {
	private $_tables = [
		'activity_logs' => ['team_id', 'person_id', 'game_id', 'team_event_id', 'newsletter_id'],
		'affiliates_people' => ['person_id'],
		'badges' => ['affiliate_id'],
		'categories' => ['affiliate_id'],
		'contacts' => ['affiliate_id'],
		'credits' => ['affiliate_id'],
		'divisions_days' => ['division_id'],
		'divisions_gameslots' => ['division_id'],
		'divisions_people' => ['person_id'],
		'events' => ['affiliate_id', 'division_id', 'event_type_id'],
		'events_connections' => ['connected_event_id'],
		'facilities' => ['region_id', 'is_open'],
		'franchises' => ['affiliate_id'],
		'franchises_people' => ['person_id'],
		'game_slots' => ['field_id', 'game_date'],
		'games_allstars' => ['score_entry_id', 'team_id'],
		'holidays' => ['affiliate_id'],
		'incidents' => ['game_id', 'team_id'],
		'leagues' => ['affiliate_id'],
		'leagues_stat_types' => ['league_id'],
		'mailing_lists' => ['affiliate_id'],
		'newsletters' => ['mailing_list_id'],
		'notes' => ['team_id', 'person_id', 'game_id', 'field_id'],
		'notices' => ['display_to', 'active'],
		'payments' => ['registration_id'],
		'people' => ['user_id', 'status'],
		'prices' => ['event_id'],
		'questionnaires' => ['affiliate_id'],
		'questions' => ['affiliate_id'],
		'regions' => ['affiliate_id'],
		'registrations' => ['person_id', 'event_id'],
		'score_details' => ['team_id'],
		'settings' => ['affiliate_id', 'person_id'],
		'spirit_entries' => ['game_id'],
		'tasks' => ['person_id'],
		'teams_people' => ['person_id', 'team_id'],
		'upload_types' => ['affiliate_id'],
		'users' => ['email'],
		'waivers' => ['affiliate_id'],
	];

	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('payments')
			->removeIndexByName('registration_id')
			->update();

		$this->table('registrations')
			->removeIndexByName('person_id')
			->update();

		foreach ($this->_tables as $table_name => $fields) {
			$table = $this->table($table_name);
			foreach ($fields as $field) {
				$table->addIndex($field);
			}
			$table->update();
		}
	}
	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		foreach ($this->_tables as $table_name => $fields) {
			$table = $this->table($table_name);
			foreach ($fields as $field) {
				$table->removeIndex($field);
			}
			$table->update();
		}
	}

}
