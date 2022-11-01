<?php
use Migrations\AbstractMigration;

class UpdateToZuluru3 extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		// Rename and otherwise tweak a bunch of columns
		$this->table('attendances')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('badges_people')
			->renameColumn('nominated_by', 'nominated_by_id')
			->renameColumn('approved_by', 'approved_by_id')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('games')
			->renameColumn('home_team', 'home_team_id')
			->renameColumn('away_team', 'away_team_id')
			->renameColumn('approved_by', 'approved_by_id')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('locks')
			->removeIndex(['key'])
			->renameColumn('key', 'name')
			->addIndex(['name'])
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->save();

		$this->table('notes')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('payments')
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->save();

		$this->table('people')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('people_people')
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->save();

		$this->table('registration_audits')
			->changeColumn('transaction_id', 'string', ['length' => 18, 'null' => true])
			->save();

		$this->table('responses')
			->renameColumn('answer', 'answer_text')
			->save();

		$this->table('score_entries')
			->removeColumn('spirit')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('spirit_entries')
			->renameColumn('most_spirited', 'most_spirited_id')
			->save();

		$this->table('task_slots')
			->renameColumn('approved_by', 'approved_by_id')
			->renameColumn('updated', 'modified')
			->save();

		$this->table('teams_people')
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->save();

		$this->table('uploads')
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->changeColumn('updated', 'datetime', ['null' => true, 'default' => null])
			->renameColumn('updated', 'modified')
			->save();

		$this->table('waivers_people')
			->changeColumn('created', 'datetime', ['null' => true, 'default' => null])
			->save();

		// Add id column
		$this->table('divisions_days')
			->addColumn('id', 'integer', ['null' => false, 'default' => null])
			->save();
		// TODO: No way to do this as part of the addColumn call?
		$this->execute('ALTER TABLE divisions_days CHANGE id id INT AUTO_INCREMENT PRIMARY KEY;');

		$this->table('leagues_stat_types')
			->addColumn('id', 'integer', ['null' => false, 'default' => null])
			->save();
		// TODO: No way to do this as part of the addColumn call?
		$this->execute('ALTER TABLE leagues_stat_types CHANGE id id INT AUTO_INCREMENT PRIMARY KEY;');

		// Fill score_entries with old data, so we have something to associate allstars with
		$this->execute('INSERT IGNORE INTO score_entries (team_id,game_id,person_id,score_for,score_against,created,status,modified) ' .
			'SELECT home_team_id,id,-1,home_score,away_score,modified,status,modified FROM games WHERE home_score IS NOT NULL');
		$this->execute('INSERT IGNORE INTO score_entries (team_id,game_id,person_id,score_for,score_against,created,status,modified) ' .
			'SELECT away_team_id,id,-1,away_score,home_score,modified,status,modified FROM games WHERE home_score IS NOT NULL');

		// New allstar structure
		$this->table('allstars')
			->addColumn('score_entry_id', 'integer', ['null' => false, 'default' => 0, 'after' => 'id'])
			->addColumn('team_id', 'integer', ['null' => false, 'default' => 0])
			->save();
		// TODOSECOND: Come up with a query to check if there's anyone that was ever nominated as all-star and is on the rosters of both teams?
		$this->execute('UPDATE allstars a, score_entries s, teams_people r, teams t, divisions d ' .
			'SET a.score_entry_id = s.id, a.team_id = r.team_id ' .
			'WHERE a.game_id = s.game_id AND s.team_id = r.team_id AND a.person_id = r.person_id AND r.team_id = t.id AND t.division_id = d.id AND d.allstars_from = \'submitter\'');
		$this->execute('UPDATE allstars a, score_entries s, games g, teams_people r, teams t, divisions d ' .
			'SET a.score_entry_id = s.id, a.team_id = r.team_id ' .
			'WHERE a.game_id = s.game_id AND s.team_id = g.home_team_id AND g.away_team_id = r.team_id AND a.person_id = r.person_id AND r.team_id = t.id AND t.division_id = d.id AND d.allstars_from = \'opponent\'');
		$this->execute('UPDATE allstars a, score_entries s, games g, teams_people r, teams t, divisions d ' .
			'SET a.score_entry_id = s.id, a.team_id = r.team_id ' .
			'WHERE a.game_id = s.game_id AND s.team_id = g.away_team_id AND g.home_team_id = r.team_id AND a.person_id = r.person_id AND r.team_id = t.id AND t.division_id = d.id AND d.allstars_from = \'opponent\'');
		$this->table('allstars')
			->removeColumn('game_id')
			->changeColumn('score_entry_id', 'integer', ['null' => false, 'default' => null])
			->rename('games_allstars')
			->save();

		// Eliminate stray spirit entries arising from old bugs
		$this->execute('DELETE FROM spirit_entries WHERE game_id IN (SELECT id FROM games WHERE status != \'normal\' AND approved_by_id > 0)');

		// Update some data
		$this->execute('UPDATE questions SET type = \'textarea\' WHERE type = \'textbox\'');
		$this->execute('UPDATE settings SET value = \'MMM d, yyyy\' WHERE name = \'date_format\' AND value = \'M j, Y\'');
		$this->execute('UPDATE settings SET value = \'MMMM d, yyyy\' WHERE name = \'date_format\' AND value = \'F j, Y\'');
		$this->execute('UPDATE settings SET value = \'dd/MM/yyyy\' WHERE name = \'date_format\' AND value = \'d/m/Y\'');
		$this->execute('UPDATE settings SET value = \'yyyy/MM/dd\' WHERE name = \'date_format\' AND value = \'Y/m/d\'');
		$this->execute('UPDATE settings SET value = \'EEE MMM d\' WHERE name = \'day_format\' AND value = \'D M j\'');
		$this->execute('UPDATE settings SET value = \'EEEE MMMM d\' WHERE name = \'day_format\' AND value = \'l F j\'');
		$this->execute('UPDATE settings SET value = \'h:mma\' WHERE name = \'time_format\' AND value = \'g:iA\'');
		$this->execute('UPDATE settings SET value = \'HH:mm\' WHERE name = \'time_format\' AND value = \'H:i\'');
		$this->execute('UPDATE settings SET name = \'ckeditor\' WHERE name = \'tiny_mce\'');

		// Eliminate allow_deposit, fixed_deposit and deposit_only fields, and replace with new online_payment_option
		$this->table('prices')
			->addColumn('online_payment_option', 'integer', ['null' => false, 'default' => 0, 'after' => 'allow_late_payment'])
			->save();
		$this->execute('UPDATE prices SET online_payment_option = 1 WHERE allow_deposit = 0');
		$this->execute('UPDATE prices SET online_payment_option = 2 WHERE allow_deposit = 1 AND fixed_deposit = 0 AND deposit_only = 0 AND minimum_deposit > 0');
		$this->execute('UPDATE prices SET online_payment_option = 3 WHERE allow_deposit = 1 AND fixed_deposit = 1 AND deposit_only = 0 AND minimum_deposit > 0');
		$this->execute('UPDATE prices SET online_payment_option = 4 WHERE allow_deposit = 1 AND fixed_deposit = 1 AND deposit_only = 1 AND minimum_deposit > 0');
		$this->execute('UPDATE prices SET online_payment_option = 5 WHERE allow_deposit = 1 AND fixed_deposit = 0 AND deposit_only = 0 AND minimum_deposit = 0');
		$this->execute('UPDATE prices SET online_payment_option = 6 WHERE allow_deposit = 1 AND fixed_deposit = 1 AND deposit_only = 1 AND minimum_deposit = 0');
		$this->table('prices')
			->removeColumn('allow_deposit')
			->removeColumn('fixed_deposit')
			->removeColumn('deposit_only')
			->save();
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		// TODOSECOND: Complete the reversal of remaining "up" changes

		// Restore allow_deposit, fixed_deposit and deposit_only fields from online_payment_option
		$this->table('prices')
			->addColumn('allow_deposit', 'boolean', ['null' => false, 'default' => 0, 'after' => 'allow_late_payment'])
			->addColumn('fixed_deposit', 'boolean', ['null' => false, 'default' => 0, 'after' => 'allow_deposit'])
			->addColumn('deposit_only', 'boolean', ['null' => false, 'default' => 0, 'after' => 'fixed_deposit'])
			->save();
		$this->execute('UPDATE prices SET allow_deposit = 1, fixed_deposit = 0, deposit_only = 0, minimum_deposit > 0 WHERE online_payment_option = 2');
		$this->execute('UPDATE prices SET allow_deposit = 1, fixed_deposit = 1, deposit_only = 0, minimum_deposit > 0 WHERE online_payment_option = 3');
		$this->execute('UPDATE prices SET allow_deposit = 1, fixed_deposit = 1, deposit_only = 1, minimum_deposit > 0 WHERE online_payment_option = 4');
		$this->execute('UPDATE prices SET allow_deposit = 1, fixed_deposit = 0, deposit_only = 0, minimum_deposit = 0 WHERE online_payment_option = 5');
		$this->execute('UPDATE prices SET allow_deposit = 1, fixed_deposit = 1, deposit_only = 1, minimum_deposit = 0 WHERE online_payment_option = 6');
		$this->table('prices')
			->removeColumn('online_payment_option')
			->save();
	}

}
