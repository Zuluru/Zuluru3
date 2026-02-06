<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;
use Phinx\Db\Table\Column;

class Install extends AbstractMigration {
	public function up() {
		$this->table('activity_logs')
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_event_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('custom', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('newsletter_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'type',
				]
			)
			->addIndex(
				[
					'type',
					'custom',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'team_event_id',
				]
			)
			->addIndex(
				[
					'newsletter_id',
				]
			)
			->create();

		$this->table('affiliates')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('affiliates_people')
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('position', Column::STRING, [
				'default' => 'player',
				'limit' => 64,
				'null' => true,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->addIndex(
				[
					'affiliate_id',
					'person_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('answers')
			->addColumn('question_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('answer', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('sort', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'question_id',
				]
			)
			->create();

		$this->table('attendances')
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_date', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_event_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('status', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('comment', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'team_event_id',
				]
			)
			->create();

		$this->table('badges')
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('category', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('handler', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('visibility', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('icon', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('refresh_from', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'category',
				]
			)
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('badges_people')
			->addColumn('badge_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('nominated_by_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('registration_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('reason', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('approved', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('approved_by_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('visible', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'badge_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('categories')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('contacts')
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('email', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('countries')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('credits')
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('amount', Column::FLOAT, [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('amount_used', Column::FLOAT, [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('notes', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created_person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('days')
			->addColumn('name', Column::STRING, [
				'default' => '',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('short_name', Column::STRING, [
				'default' => '',
				'limit' => 3,
				'null' => false,
			])
			->create();

		$this->table('divisions')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('open', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('close', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('ratio_rule', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('current_round', Column::STRING, [
				'default' => '1',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('roster_deadline', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('roster_rule', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_open', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('schedule_type', Column::STRING, [
				'default' => 'none',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('games_before_repeat', Column::INTEGER, [
				'default' => '4',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('allstars', Column::STRING, [
				'default' => 'never',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('exclude_teams', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('coord_list', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('capt_list', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('email_after', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('finalize_after', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('roster_method', Column::STRING, [
				'default' => 'invite',
				'limit' => 6,
				'null' => false,
			])
			->addColumn('league_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rating_calculator', Column::STRING, [
				'default' => 'none',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('flag_membership', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('flag_roster_conflict', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('flag_schedule_conflict', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('allstars_from', Column::STRING, [
				'default' => 'opponent',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('header', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('footer', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('double_booking', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('most_spirited', Column::STRING, [
				'default' => 'never',
				'limit' => 32,
				'null' => false,
			])
			->addIndex(
				[
					'league_id',
				]
			)
			->create();

		$this->table('divisions_days')
			->addColumn('division_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('day_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'division_id',
				]
			)
			->create();

		$this->table('divisions_gameslots')
			->addColumn('division_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_slot_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'division_id',
				]
			)
			->create();

		$this->table('divisions_people')
			->addColumn('division_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('position', Column::STRING, [
				'default' => 'coordinator',
				'limit' => 64,
				'null' => true,
			])
			->addIndex(
				[
					'division_id',
					'person_id',
				]
			)
			->addIndex(
				[
					'division_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('event_types')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('events')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('event_type_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('open', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('close', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('open_cap', Column::INTEGER, [
				'default' => '0',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('women_cap', Column::INTEGER, [
				'default' => '0',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('multiple', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => true,
			])
			->addColumn('questionnaire_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('custom', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('division_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('season_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'name',
				],
				['unique' => true]
			)
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->addIndex(
				[
					'division_id',
				]
			)
			->addIndex(
				[
					'event_type_id',
				]
			)
			->create();

		$this->table('events_connections')
			->addColumn('event_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('connection', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('connected_event_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'event_id',
				]
			)
			->addIndex(
				[
					'connected_event_id',
				]
			)
			->create();

		$this->table('facilities')
			->addColumn('is_open', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('code', Column::STRING, [
				'default' => null,
				'limit' => 3,
				'null' => true,
			])
			->addColumn('location_street', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_city', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_province', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('parking', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('entrances', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('region_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('driving_directions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('parking_details', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('transit_directions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('biking_directions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('washrooms', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('public_instructions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('site_instructions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('sponsor', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('sport', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addIndex(
				[
					'region_id',
				]
			)
			->addIndex(
				[
					'is_open',
				]
			)
			->create();

		$this->table('field_ranking_stats')
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'game_id',
					'team_id',
				]
			)
			->create();

		$this->table('fields')
			->addColumn('num', Column::STRING, [
				'default' => null,
				'limit' => 15,
				'null' => true,
			])
			->addColumn('is_open', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('indoor', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('surface', Column::STRING, [
				'default' => 'grass',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('rating', Column::STRING, [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('facility_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('latitude', Column::FLOAT, [
				'default' => null,
				'precision' => 24,
				'scale' => 4,
				'null' => true,
			])
			->addColumn('longitude', Column::FLOAT, [
				'default' => null,
				'precision' => 24,
				'scale' => 4,
				'null' => true,
			])
			->addColumn('angle', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('length', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('width', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('zoom', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('layout_url', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('availability', Column::STRING, [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('sport', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addIndex(
				[
					'facility_id',
				]
			)
			->create();

		$this->table('franchises')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('website', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('franchises_people')
			->addColumn('franchise_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'franchise_id',
				]
			)
			->addIndex(
				[
					'franchise_id',
					'person_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('franchises_teams')
			->addColumn('franchise_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'franchise_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('game_slots')
			->addColumn('field_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_date', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_start', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_end', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('assigned', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'field_id',
				]
			)
			->addIndex(
				[
					'game_date',
				]
			)
			->create();

		$this->table('games')
			->addColumn('division_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('round', Column::STRING, [
				'default' => '1',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('tournament_pool', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('placement', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_dependency_type', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('home_dependency_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_dependency_type', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('away_dependency_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_score', Column::INTEGER, [
				'default' => null,
				'limit' => 4,
				'null' => true,
			])
			->addColumn('away_score', Column::INTEGER, [
				'default' => null,
				'limit' => 4,
				'null' => true,
			])
			->addColumn('rating_points', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('approved_by_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('status', Column::STRING, [
				'default' => 'normal',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('published', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('type', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('pool_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_pool_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_pool_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_slot_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('rescheduled_slot', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_field_rank', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_field_rank', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_carbon_flip', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'home_team_id',
				]
			)
			->addIndex(
				[
					'away_team_id',
				]
			)
			->addIndex(
				[
					'division_id',
				]
			)
			->create();

		$this->table('games_allstars')
			->addColumn('score_entry_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'score_entry_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('user_groups')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('level', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('groups_people')
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('group_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'group_id',
				]
			)
			->create();

		$this->table('holidays')
			->addColumn(Column::DATE, Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('incidents')
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('details', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('reporting_user_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('leagues')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('sport', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('season', Column::STRING, [
				'default' => 'None',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('open', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('close', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('is_open', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('schedule_attempts', Column::INTEGER, [
				'default' => '100',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('display_sotg', Column::STRING, [
				'default' => 'all',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('sotg_questions', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('numeric_sotg', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('expected_max_score', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_tracking', Column::STRING, [
				'default' => 'never',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('tie_breaker', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('carbon_flip', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('leagues_stat_types')
			->addColumn('league_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'league_id',
				]
			)
			->create();

		$this->table('locks')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('user_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'name',
				]
			)
			->create();

		$this->table('logs')
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('login_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('controller', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('action', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('query', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('params', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('form', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('memory', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('ms', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('mailing_lists')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('opt_out', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('rule', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('membership_types')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('description', Column::STRING, [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('priority', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('report_as', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('badge', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('newsletters')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('from_email', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('to_email', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('subject', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn(Column::TEXT, Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('target', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('delay', Column::INTEGER, [
				'default' => '10',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('batch_size', Column::INTEGER, [
				'default' => '100',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('personalize', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('mailing_list_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('reply_to', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addIndex(
				[
					'mailing_list_id',
				]
			)
			->create();

		$this->table('notes')
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('field_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('visibility', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created_person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('note', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'team_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'field_id',
				]
			)
			->create();

		$this->table('notices')
			->addColumn('sort', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('display_to', Column::STRING, [
				'default' => 'player',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('repeat_on', Column::STRING, [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('notice', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('effective_date', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'display_to',
				]
			)
			->addIndex(
				[
					'active',
				]
			)
			->create();

		$this->table('notices_people')
			->addColumn('notice_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('remind', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'notice_id',
				]
			)
			->addIndex(
				[
					'notice_id',
					'person_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('payments')
			->addColumn('registration_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('registration_audit_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('payment_type', Column::STRING, [
				'default' => 'Full',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('payment_amount', Column::FLOAT, [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('refunded_amount', Column::FLOAT, [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('notes', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created_person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('updated_person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('payment_method', Column::STRING, [
				'default' => 'Other',
				'limit' => 32,
				'null' => false,
			])
			->addIndex(
				[
					'registration_id',
				]
			)
			->create();

		$this->table('people')
			->addColumn('first_name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('last_name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('publish_email', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('home_phone', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_home_phone', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('work_phone', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('work_ext', Column::STRING, [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('publish_work_phone', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('mobile_phone', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_mobile_phone', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('addr_street', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('addr_city', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('addr_prov', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('addr_country', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('addr_postalcode', Column::STRING, [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('gender', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('birthdate', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('height', Column::INTEGER, [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('shirt_size', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('status', Column::STRING, [
				'default' => 'new',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('has_dog', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('contact_for_feedback', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('complete', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('twitter_token', Column::STRING, [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('twitter_secret', Column::STRING, [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('user_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('show_gravatar', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_first_name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('alternate_last_name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('alternate_email', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('publish_alternate_email', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_work_phone', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('alternate_work_ext', Column::STRING, [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('publish_alternate_work_phone', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_mobile_phone', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_alternate_mobile_phone', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('modified', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('gender_description', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('roster_designation', Column::STRING, [
				'default' => null,
				'limit' => 6,
				'null' => false,
			])
			->addIndex(
				[
					'user_id',
				]
			)
			->addIndex(
				[
					'status',
				]
			)
			->create();

		$this->table('people_people')
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('relative_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('approved', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'relative_id',
				]
			)
			->create();

		$this->table('pools')
			->addColumn('division_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('stage', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 16,
				'null' => false,
			])
			->addIndex(
				[
					'division_id',
				]
			)
			->create();

		$this->table('pools_teams')
			->addColumn('pool_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('alias', Column::STRING, [
				'default' => null,
				'limit' => 4,
				'null' => false,
			])
			->addColumn('dependency_type', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('dependency_ordinal', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('dependency_pool_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('dependency_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'pool_id',
				]
			)
			->create();

		$this->table('preregistrations')
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('event_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'event_id',
				]
			)
			->create();

		$this->table('prices')
			->addColumn('event_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('cost', Column::FLOAT, [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('tax1', Column::FLOAT, [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('tax2', Column::FLOAT, [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('open', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('close', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('register_rule', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('minimum_deposit', Column::FLOAT, [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('allow_late_payment', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('online_payment_option', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('allow_reservations', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('reservation_duration', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'event_id',
				]
			)
			->create();

		$this->table('provinces')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('questionnaires')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('questionnaires_questions')
			->addColumn('questionnaire_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('question_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('sort', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('required', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'questionnaire_id',
					'question_id',
				],
				['unique' => true]
			)
			->addIndex(
				[
					'questionnaire_id',
				]
			)
			->addIndex(
				[
					'question_id',
				]
			)
			->create();

		$this->table('questions')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('question', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 20,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('anonymous', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('regions')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('registration_answers', ['id' => false, 'primary_key' => ['registration_id', 'qkey']])
			->addColumn('registration_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('qkey', Column::STRING, [
				'default' => '',
				'limit' => 255,
				'null' => false,
			])
			->addColumn('akey', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->create();

		$this->table('registration_audits')
			->addColumn('response_code', Column::INTEGER, [
				'default' => '0',
				'limit' => 5,
				'null' => false,
				'signed' => false,
			])
			->addColumn('iso_code', Column::INTEGER, [
				'default' => '0',
				'limit' => 5,
				'null' => false,
				'signed' => false,
			])
			->addColumn(Column::DATE, Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn(Column::TIME, Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('transaction_id', Column::STRING, [
				'default' => null,
				'limit' => 18,
				'null' => true,
			])
			->addColumn('approval_code', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('transaction_name', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('charge_total', Column::DECIMAL, [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('cardholder', Column::STRING, [
				'default' => null,
				'limit' => 40,
				'null' => true,
			])
			->addColumn('expiry', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('f4l4', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('card', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('message', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => true,
			])
			->addColumn('issuer', Column::STRING, [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('issuer_invoice', Column::STRING, [
				'default' => null,
				'limit' => 20,
				'null' => true,
			])
			->addColumn('issuer_confirmation', Column::STRING, [
				'default' => null,
				'limit' => 15,
				'null' => true,
			])
			->create();

		$this->table('registrations')
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('event_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('payment', Column::STRING, [
				'default' => 'Unpaid',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('notes', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('total_amount', Column::FLOAT, [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('price_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('deposit_amount', Column::FLOAT, [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('reservation_expires', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('delete_on_expiry', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'event_id',
					'payment',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'event_id',
				]
			)
			->create();

		$this->table('reports')
			->addColumn('report', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('params', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('failures', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->create();

		$this->table('responses')
			->addColumn('event_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('registration_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('question_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('answer_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('answer_text', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'event_id',
				]
			)
			->addIndex(
				[
					'registration_id',
				]
			)
			->create();

		$this->table('roster_roles')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('description', Column::STRING, [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_player', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_extended_player', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_regular', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_privileged', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_required', Column::BOOLEAN, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();

		$this->table('score_detail_stats')
			->addColumn('score_detail_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'score_detail_id',
				]
			)
			->create();

		$this->table('score_details')
			->addColumn('game_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created_team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('score_from', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('play', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('points', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('score_entries')
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('score_for', Column::INTEGER, [
				'default' => '0',
				'limit' => 4,
				'null' => true,
			])
			->addColumn('score_against', Column::INTEGER, [
				'default' => '0',
				'limit' => 4,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('status', Column::STRING, [
				'default' => 'normal',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('home_carbon_flip', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('gender_ratio', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addIndex(
				[
					'team_id',
					'game_id',
				]
			)
			->addIndex(
				[
					'game_id',
				]
			)
			->create();

		$this->table('sessions', ['id' => false, 'primary_key' => ['id']])
			->addColumn('id', Column::STRING, [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('data', Column::TEXT, [
				'default' => null,
				'limit' => 4294967295,
				'null' => true,
			])
			->addColumn('expires', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->create();

		$this->table('settings')
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('category', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => '',
				'limit' => 50,
				'null' => false,
			])
			->addColumn('value', Column::TEXT, [
				'default' => null,
				'limit' => 4294967295,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('skills')
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('sport', Column::STRING, [
				'default' => 'ultimate',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('enabled', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('skill_level', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('year_started', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('spirit_entries')
			->addColumn('created_team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('entered_sotg', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('score_entry_penalty', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q1', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q2', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q3', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q4', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q5', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q6', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q7', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q8', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q9', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q10', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('comments', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('highlights', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('most_spirited_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('subs', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'team_id',
					'game_id',
				]
			)
			->addIndex(
				[
					'created_team_id',
					'game_id',
				]
			)
			->addIndex(
				[
					'game_id',
				]
			)
			->create();

		$this->table('stat_types')
			->addColumn('sport', Column::STRING, [
				'default' => 'ultimate',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('positions', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('abbr', Column::STRING, [
				'default' => null,
				'limit' => 8,
				'null' => false,
			])
			->addColumn('internal_name', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('sort', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('class', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('type', Column::STRING, [
				'default' => null,
				'limit' => 16,
				'null' => false,
			])
			->addColumn('base', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('handler', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('sum_function', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('formatter_function', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('validation', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->create();

		$this->table('stats')
			->addColumn('game_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('value', Column::FLOAT, [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'game_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('subscriptions')
			->addColumn('mailing_list_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('subscribed', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'mailing_list_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'mailing_list_id',
					'person_id',
				]
			)
			->create();

		$this->table('task_slots')
			->addColumn('task_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('task_date', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('task_start', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('task_end', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('approved', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('approved_by_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'task_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('tasks')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('category_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('notes', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('auto_approve', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('allow_signup', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('team_events')
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('description', Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('website', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn(Column::DATE, Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('start', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('end', Column::TIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('location_name', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_street', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_city', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_province', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('team_site_ranking', ['id' => false])
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('site_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'team_id',
					'site_id',
				]
			)
			->addIndex(
				[
					'team_id',
					'rank',
				]
			)
			->create();

		$this->table('teams')
			->addColumn('name', Column::STRING, [
				'default' => '',
				'limit' => 100,
				'null' => false,
			])
			->addColumn('division_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('website', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('shirt_colour', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('home_field_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('region_preference_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('open_roster', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('rating', Column::INTEGER, [
				'default' => '1500',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('track_attendance', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('attendance_reminder', Column::INTEGER, [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('attendance_summary', Column::INTEGER, [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('attendance_notification', Column::INTEGER, [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('initial_rating', Column::INTEGER, [
				'default' => '1500',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('initial_seed', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('seed', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('flickr_user', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('flickr_set', Column::STRING, [
				'default' => null,
				'limit' => 24,
				'null' => true,
			])
			->addColumn('flickr_ban', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('logo', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('short_name', Column::STRING, [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('twitter_user', Column::STRING, [
				'default' => null,
				'limit' => 64,
				'null' => true,
			])
			->addIndex(
				[
					'name',
				]
			)
			->addIndex(
				[
					'division_id',
				]
			)
			->create();

		$this->table('teams_facilities')
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('facility_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('teams_people')
			->addColumn('team_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('role', Column::STRING, [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('status', Column::INTEGER, [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('number', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('position', Column::STRING, [
				'default' => 'unspecified',
				'limit' => 32,
				'null' => false,
			])
			->addIndex(
				[
					'team_id',
					'person_id',
				]
			)
			->addIndex(
				[
					'person_id',
				]
			)
			->addIndex(
				[
					'team_id',
				]
			)
			->create();

		$this->table('upload_types')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('uploads')
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('type_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('valid_from', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('valid_until', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('filename', Column::STRING, [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('approved', Column::BOOLEAN, [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		$this->table('users')
			->addColumn('user_name', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('password', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('email', Column::STRING, [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('last_login', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('client_ip', Column::STRING, [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addIndex(
				[
					'user_name',
				],
				['unique' => true]
			)
			->addIndex(
				[
					'email',
				]
			)
			->create();

		$this->table('waivers')
			->addColumn('name', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('description', Column::STRING, [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn(Column::TEXT, Column::TEXT, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('active', Column::BOOLEAN, [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('expiry_type', Column::STRING, [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('start_month', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('start_day', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('end_month', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('end_day', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('duration', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', Column::INTEGER, [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addIndex(
				[
					'affiliate_id',
				]
			)
			->create();

		$this->table('waivers_people')
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', Column::DATETIME, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('waiver_id', Column::INTEGER, [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('valid_from', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('valid_until', Column::DATE, [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addIndex(
				[
					'person_id',
				]
			)
			->create();

		if (!defined('PHPUNIT_TESTSUITE') || !PHPUNIT_TESTSUITE) {
			$migrations = new Migrations();
			$seeds = [
				'Affiliates', 'Notices', 'Settings', 'Waivers',
				'Countries', 'Provinces', 'Regions', 'Days',
				'Badges', 'EventTypes', 'MembershipTypes', 'StatTypes', 'RosterRoles',
				'Users', 'People', 'AffiliatesPeople', 'UserGroups', 'GroupsPeople',
			];
			foreach ($seeds as $seed) {
				$migrations->seed(['seed' => "{$seed}Seed"]);
			}
		}
	}

	public function down() {
		$this->table('activity_logs')->drop()->save();
		$this->table('affiliates')->drop()->save();
		$this->table('affiliates_people')->drop()->save();
		$this->table('answers')->drop()->save();
		$this->table('attendances')->drop()->save();
		$this->table('badges')->drop()->save();
		$this->table('badges_people')->drop()->save();
		$this->table('categories')->drop()->save();
		$this->table('contacts')->drop()->save();
		$this->table('countries')->drop()->save();
		$this->table('credits')->drop()->save();
		$this->table('days')->drop()->save();
		$this->table('divisions')->drop()->save();
		$this->table('divisions_days')->drop()->save();
		$this->table('divisions_gameslots')->drop()->save();
		$this->table('divisions_people')->drop()->save();
		$this->table('event_types')->drop()->save();
		$this->table('events')->drop()->save();
		$this->table('events_connections')->drop()->save();
		$this->table('facilities')->drop()->save();
		$this->table('field_ranking_stats')->drop()->save();
		$this->table('fields')->drop()->save();
		$this->table('franchises')->drop()->save();
		$this->table('franchises_people')->drop()->save();
		$this->table('franchises_teams')->drop()->save();
		$this->table('game_slots')->drop()->save();
		$this->table('games')->drop()->save();
		$this->table('games_allstars')->drop()->save();
		$this->table('user_groups')->drop()->save();
		$this->table('groups_people')->drop()->save();
		$this->table('holidays')->drop()->save();
		$this->table('incidents')->drop()->save();
		$this->table('leagues')->drop()->save();
		$this->table('leagues_stat_types')->drop()->save();
		$this->table('locks')->drop()->save();
		$this->table('logs')->drop()->save();
		$this->table('mailing_lists')->drop()->save();
		$this->table('membership_types')->drop()->save();
		$this->table('newsletters')->drop()->save();
		$this->table('notes')->drop()->save();
		$this->table('notices')->drop()->save();
		$this->table('notices_people')->drop()->save();
		$this->table('payments')->drop()->save();
		$this->table('people')->drop()->save();
		$this->table('people_people')->drop()->save();
		$this->table('pools')->drop()->save();
		$this->table('pools_teams')->drop()->save();
		$this->table('preregistrations')->drop()->save();
		$this->table('prices')->drop()->save();
		$this->table('provinces')->drop()->save();
		$this->table('questionnaires')->drop()->save();
		$this->table('questionnaires_questions')->drop()->save();
		$this->table('questions')->drop()->save();
		$this->table('regions')->drop()->save();
		$this->table('registration_answers')->drop()->save();
		$this->table('registration_audits')->drop()->save();
		$this->table('registrations')->drop()->save();
		$this->table('reports')->drop()->save();
		$this->table('responses')->drop()->save();
		$this->table('roster_roles')->drop()->save();
		$this->table('score_detail_stats')->drop()->save();
		$this->table('score_details')->drop()->save();
		$this->table('score_entries')->drop()->save();
		$this->table('sessions')->drop()->save();
		$this->table('settings')->drop()->save();
		$this->table('skills')->drop()->save();
		$this->table('spirit_entries')->drop()->save();
		$this->table('stat_types')->drop()->save();
		$this->table('stats')->drop()->save();
		$this->table('subscriptions')->drop()->save();
		$this->table('task_slots')->drop()->save();
		$this->table('tasks')->drop()->save();
		$this->table('team_events')->drop()->save();
		$this->table('team_site_ranking')->drop()->save();
		$this->table('teams')->drop()->save();
		$this->table('teams_facilities')->drop()->save();
		$this->table('teams_people')->drop()->save();
		$this->table('upload_types')->drop()->save();
		$this->table('uploads')->drop()->save();
		$this->table('users')->drop()->save();
		$this->table('waivers')->drop()->save();
		$this->table('waivers_people')->drop()->save();
	}
}
