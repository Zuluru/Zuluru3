<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class Install extends AbstractMigration {
	public function up() {
		$this->table('activity_logs')
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_event_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('custom', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('newsletter_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('affiliates_people')
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('position', 'string', [
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
			->addColumn('question_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('answer', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('sort', 'integer', [
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
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_date', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_event_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('status', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('comment', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
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
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('category', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('handler', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('visibility', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('icon', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('refresh_from', 'integer', [
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
			->addColumn('badge_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('nominated_by_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('registration_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('reason', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('approved', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('approved_by_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('visible', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('email', 'string', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('credits')
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('amount', 'float', [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('amount_used', 'float', [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('notes', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created_person_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => '',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('short_name', 'string', [
				'default' => '',
				'limit' => 3,
				'null' => false,
			])
			->create();

		$this->table('divisions')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('open', 'date', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('close', 'date', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('ratio_rule', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('current_round', 'string', [
				'default' => '1',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('roster_deadline', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('roster_rule', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_open', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('schedule_type', 'string', [
				'default' => 'none',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('games_before_repeat', 'integer', [
				'default' => '4',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('allstars', 'string', [
				'default' => 'never',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('exclude_teams', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('coord_list', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('capt_list', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('email_after', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('finalize_after', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('roster_method', 'string', [
				'default' => 'invite',
				'limit' => 6,
				'null' => false,
			])
			->addColumn('league_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rating_calculator', 'string', [
				'default' => 'none',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('flag_membership', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('flag_roster_conflict', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('flag_schedule_conflict', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('allstars_from', 'string', [
				'default' => 'opponent',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('header', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('footer', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('double_booking', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('most_spirited', 'string', [
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
			->addColumn('division_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('day_id', 'integer', [
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
			->addColumn('division_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_slot_id', 'integer', [
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
			->addColumn('division_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('position', 'string', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('events')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('event_type_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('open', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('close', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('open_cap', 'integer', [
				'default' => '0',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('women_cap', 'integer', [
				'default' => '0',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('multiple', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => true,
			])
			->addColumn('questionnaire_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('custom', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('division_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('season_id', 'integer', [
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
			->addColumn('event_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('connection', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('connected_event_id', 'integer', [
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
			->addColumn('is_open', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('code', 'string', [
				'default' => null,
				'limit' => 3,
				'null' => true,
			])
			->addColumn('location_street', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_city', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_province', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('parking', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('entrances', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('region_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('driving_directions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('parking_details', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('transit_directions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('biking_directions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('washrooms', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('public_instructions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('site_instructions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('sponsor', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('sport', 'string', [
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
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', 'integer', [
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
			->addColumn('num', 'string', [
				'default' => null,
				'limit' => 15,
				'null' => true,
			])
			->addColumn('is_open', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('indoor', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('surface', 'string', [
				'default' => 'grass',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('rating', 'string', [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('facility_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('latitude', 'float', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('longitude', 'float', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('angle', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('length', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('width', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('zoom', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('layout_url', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('availability', 'string', [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('sport', 'string', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('website', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('franchise_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
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
			->addColumn('franchise_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
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
			->addColumn('field_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_date', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_start', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('game_end', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('assigned', 'boolean', [
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
			->addColumn('division_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('round', 'string', [
				'default' => '1',
				'limit' => 10,
				'null' => false,
			])
			->addColumn('tournament_pool', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('placement', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_dependency_type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('home_dependency_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_dependency_type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('away_dependency_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_score', 'integer', [
				'default' => null,
				'limit' => 4,
				'null' => true,
			])
			->addColumn('away_score', 'integer', [
				'default' => null,
				'limit' => 4,
				'null' => true,
			])
			->addColumn('rating_points', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('approved_by_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('status', 'string', [
				'default' => 'normal',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('published', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('type', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('pool_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_pool_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_pool_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_slot_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('rescheduled_slot', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_field_rank', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('away_field_rank', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('home_carbon_flip', 'integer', [
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
			->addColumn('score_entry_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
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

		$this->table('groups')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('level', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('groups_people')
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('group_id', 'integer', [
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
			->addColumn('date', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('details', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('reporting_user_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('sport', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('season', 'string', [
				'default' => 'None',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('open', 'date', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('close', 'date', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('is_open', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('schedule_attempts', 'integer', [
				'default' => '100',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('display_sotg', 'string', [
				'default' => 'all',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('sotg_questions', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('numeric_sotg', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('expected_max_score', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_tracking', 'string', [
				'default' => 'never',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('tie_breaker', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('carbon_flip', 'boolean', [
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
			->addColumn('league_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('login_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('controller', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('action', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => false,
			])
			->addColumn('query', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('params', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('form', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('memory', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('ms', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->create();

		$this->table('mailing_lists')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('opt_out', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('rule', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('description', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('priority', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('report_as', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('badge', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('newsletters')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('from_email', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('to_email', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('subject', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('text', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('target', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('delay', 'integer', [
				'default' => '10',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('batch_size', 'integer', [
				'default' => '100',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('personalize', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('mailing_list_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('reply_to', 'string', [
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
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('field_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('visibility', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created_person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('note', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
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
			->addColumn('sort', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('display_to', 'string', [
				'default' => 'player',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('repeat_on', 'string', [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('notice', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('effective_date', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('notice_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('remind', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('registration_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('registration_audit_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('payment_type', 'string', [
				'default' => 'Full',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('payment_amount', 'float', [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('refunded_amount', 'float', [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('notes', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created_person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('updated_person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('payment_method', 'string', [
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
			->addColumn('first_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('last_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('publish_email', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('home_phone', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_home_phone', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('work_phone', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('work_ext', 'string', [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('publish_work_phone', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('mobile_phone', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_mobile_phone', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('addr_street', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('addr_city', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('addr_prov', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('addr_country', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('addr_postalcode', 'string', [
				'default' => null,
				'limit' => 7,
				'null' => true,
			])
			->addColumn('gender', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('birthdate', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('height', 'integer', [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('shirt_size', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('status', 'string', [
				'default' => 'new',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('has_dog', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('contact_for_feedback', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('complete', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('twitter_token', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('twitter_secret', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('show_gravatar', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_first_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('alternate_last_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('alternate_email', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('publish_alternate_email', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_work_phone', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('alternate_work_ext', 'string', [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('publish_alternate_work_phone', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('alternate_mobile_phone', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('publish_alternate_mobile_phone', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('modified', 'date', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('gender_description', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('roster_designation', 'string', [
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
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('relative_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('approved', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('division_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('stage', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('type', 'string', [
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
			->addColumn('pool_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('alias', 'string', [
				'default' => null,
				'limit' => 4,
				'null' => false,
			])
			->addColumn('dependency_type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('dependency_ordinal', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('dependency_pool_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('dependency_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
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
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('event_id', 'integer', [
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
			->addColumn('event_id', 'integer', [
				'default' => '1',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('cost', 'float', [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('tax1', 'float', [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('tax2', 'float', [
				'default' => null,
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('open', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('close', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('register_rule', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('minimum_deposit', 'float', [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('allow_late_payment', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('online_payment_option', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('allow_reservations', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('reservation_duration', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->create();

		$this->table('questionnaires')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('questionnaire_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('question_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('sort', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('required', 'boolean', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('question', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 20,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('anonymous', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('registration_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('qkey', 'string', [
				'default' => '',
				'limit' => 255,
				'null' => false,
			])
			->addColumn('akey', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->create();

		$this->table('registration_audits')
			->addColumn('response_code', 'integer', [
				'default' => '0',
				'limit' => 5,
				'null' => false,
				'signed' => false,
			])
			->addColumn('iso_code', 'integer', [
				'default' => '0',
				'limit' => 5,
				'null' => false,
				'signed' => false,
			])
			->addColumn('date', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('time', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('transaction_id', 'string', [
				'default' => null,
				'limit' => 18,
				'null' => true,
			])
			->addColumn('approval_code', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('transaction_name', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('charge_total', 'decimal', [
				'default' => '0.00',
				'null' => false,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('cardholder', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => true,
			])
			->addColumn('expiry', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('f4l4', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('card', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('message', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => true,
			])
			->addColumn('issuer', 'string', [
				'default' => null,
				'limit' => 30,
				'null' => true,
			])
			->addColumn('issuer_invoice', 'string', [
				'default' => null,
				'limit' => 20,
				'null' => true,
			])
			->addColumn('issuer_confirmation', 'string', [
				'default' => null,
				'limit' => 15,
				'null' => true,
			])
			->create();

		$this->table('registrations')
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('event_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('payment', 'string', [
				'default' => 'Unpaid',
				'limit' => 16,
				'null' => false,
			])
			->addColumn('notes', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('total_amount', 'float', [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('price_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('deposit_amount', 'float', [
				'default' => '0.00',
				'null' => true,
				'precision' => 7,
				'scale' => 2,
			])
			->addColumn('reservation_expires', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('delete_on_expiry', 'boolean', [
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
			->addColumn('report', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('params', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('failures', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->create();

		$this->table('responses')
			->addColumn('event_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('registration_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('question_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('answer_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('answer_text', 'text', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('description', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_player', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_extended_player', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_regular', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_privileged', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('is_required', 'boolean', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();

		$this->table('score_detail_stats')
			->addColumn('score_detail_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', 'integer', [
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
			->addColumn('game_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created_team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('score_from', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('play', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('points', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('team_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('game_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('score_for', 'integer', [
				'default' => '0',
				'limit' => 4,
				'null' => true,
			])
			->addColumn('score_against', 'integer', [
				'default' => '0',
				'limit' => 4,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('status', 'string', [
				'default' => 'normal',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('home_carbon_flip', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('gender_ratio', 'string', [
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
			->addColumn('id', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('data', 'text', [
				'default' => null,
				'limit' => 4294967295,
				'null' => true,
			])
			->addColumn('expires', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->create();

		$this->table('settings')
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('category', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => '',
				'limit' => 50,
				'null' => false,
			])
			->addColumn('value', 'text', [
				'default' => null,
				'limit' => 4294967295,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('sport', 'string', [
				'default' => 'ultimate',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('enabled', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('skill_level', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('year_started', 'integer', [
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
			->addColumn('created_team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('game_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('entered_sotg', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('score_entry_penalty', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q1', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q2', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q3', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q4', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q5', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q6', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q7', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q8', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q9', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('q10', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('comments', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('highlights', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('most_spirited_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('subs', 'text', [
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
			->addColumn('sport', 'string', [
				'default' => 'ultimate',
				'limit' => 32,
				'null' => false,
			])
			->addColumn('positions', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('abbr', 'string', [
				'default' => null,
				'limit' => 8,
				'null' => false,
			])
			->addColumn('internal_name', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('sort', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('class', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 16,
				'null' => false,
			])
			->addColumn('base', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('handler', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('sum_function', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('formatter_function', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->addColumn('validation', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => true,
			])
			->create();

		$this->table('stats')
			->addColumn('game_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('stat_type_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('value', 'float', [
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
			->addColumn('mailing_list_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('subscribed', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('task_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('task_date', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('task_start', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('task_end', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('approved', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('approved_by_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('category_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('notes', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('auto_approve', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('allow_signup', 'boolean', [
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
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('website', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('date', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('start', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('end', 'time', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('location_name', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_street', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_city', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('location_province', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
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
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('site_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', 'integer', [
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
			->addColumn('name', 'string', [
				'default' => '',
				'limit' => 100,
				'null' => false,
			])
			->addColumn('division_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('website', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('shirt_colour', 'string', [
				'default' => null,
				'limit' => 50,
				'null' => true,
			])
			->addColumn('home_field_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('region_preference_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('open_roster', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('rating', 'integer', [
				'default' => '1500',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('track_attendance', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('attendance_reminder', 'integer', [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('attendance_summary', 'integer', [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('attendance_notification', 'integer', [
				'default' => '-1',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('initial_rating', 'integer', [
				'default' => '1500',
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('initial_seed', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('seed', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('flickr_user', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->addColumn('flickr_set', 'string', [
				'default' => null,
				'limit' => 24,
				'null' => true,
			])
			->addColumn('flickr_ban', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('logo', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('short_name', 'string', [
				'default' => null,
				'limit' => 6,
				'null' => true,
			])
			->addColumn('twitter_user', 'string', [
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
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('facility_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('rank', 'integer', [
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
			->addColumn('team_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('person_id', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('role', 'string', [
				'default' => null,
				'limit' => 16,
				'null' => true,
			])
			->addColumn('status', 'integer', [
				'default' => '0',
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('number', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('position', 'string', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('type_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('valid_from', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('valid_until', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('filename', 'string', [
				'default' => null,
				'limit' => 128,
				'null' => false,
			])
			->addColumn('approved', 'boolean', [
				'default' => false,
				'limit' => null,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
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
			->addColumn('user_name', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('password', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('email', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => true,
			])
			->addColumn('last_login', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('client_ip', 'string', [
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
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('description', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('text', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('active', 'boolean', [
				'default' => true,
				'limit' => null,
				'null' => false,
			])
			->addColumn('expiry_type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('start_month', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('start_day', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('end_month', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('end_day', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('duration', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => true,
			])
			->addColumn('affiliate_id', 'integer', [
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
			->addColumn('person_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('waiver_id', 'integer', [
				'default' => null,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('valid_from', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('valid_until', 'date', [
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

		$migrations = new Migrations();
		$seeds = [
			'Affiliates', 'Notices', 'Settings', 'Waivers',
			'Countries', 'Provinces', 'Regions', 'Days',
			'Badges', 'EventTypes', 'MembershipTypes', 'StatTypes', 'RosterRoles',
			'Users', 'People', 'AffiliatesPeople', 'Groups', 'GroupsPeople',
		];
		foreach ($seeds as $seed) {
			$migrations->seed(['seed' => "{$seed}Seed"]);
		}
	}

	public function down() {
		$this->dropTable('activity_logs');
		$this->dropTable('affiliates');
		$this->dropTable('affiliates_people');
		$this->dropTable('answers');
		$this->dropTable('attendances');
		$this->dropTable('badges');
		$this->dropTable('badges_people');
		$this->dropTable('categories');
		$this->dropTable('contacts');
		$this->dropTable('countries');
		$this->dropTable('credits');
		$this->dropTable('days');
		$this->dropTable('divisions');
		$this->dropTable('divisions_days');
		$this->dropTable('divisions_gameslots');
		$this->dropTable('divisions_people');
		$this->dropTable('event_types');
		$this->dropTable('events');
		$this->dropTable('events_connections');
		$this->dropTable('facilities');
		$this->dropTable('field_ranking_stats');
		$this->dropTable('fields');
		$this->dropTable('franchises');
		$this->dropTable('franchises_people');
		$this->dropTable('franchises_teams');
		$this->dropTable('game_slots');
		$this->dropTable('games');
		$this->dropTable('games_allstars');
		$this->dropTable('groups');
		$this->dropTable('groups_people');
		$this->dropTable('holidays');
		$this->dropTable('incidents');
		$this->dropTable('leagues');
		$this->dropTable('leagues_stat_types');
		$this->dropTable('locks');
		$this->dropTable('logs');
		$this->dropTable('mailing_lists');
		$this->dropTable('membership_types');
		$this->dropTable('newsletters');
		$this->dropTable('notes');
		$this->dropTable('notices');
		$this->dropTable('notices_people');
		$this->dropTable('payments');
		$this->dropTable('people');
		$this->dropTable('people_people');
		$this->dropTable('pools');
		$this->dropTable('pools_teams');
		$this->dropTable('preregistrations');
		$this->dropTable('prices');
		$this->dropTable('provinces');
		$this->dropTable('questionnaires');
		$this->dropTable('questionnaires_questions');
		$this->dropTable('questions');
		$this->dropTable('regions');
		$this->dropTable('registration_answers');
		$this->dropTable('registration_audits');
		$this->dropTable('registrations');
		$this->dropTable('reports');
		$this->dropTable('responses');
		$this->dropTable('roster_roles');
		$this->dropTable('score_detail_stats');
		$this->dropTable('score_details');
		$this->dropTable('score_entries');
		$this->dropTable('sessions');
		$this->dropTable('settings');
		$this->dropTable('skills');
		$this->dropTable('spirit_entries');
		$this->dropTable('stat_types');
		$this->dropTable('stats');
		$this->dropTable('subscriptions');
		$this->dropTable('task_slots');
		$this->dropTable('tasks');
		$this->dropTable('team_events');
		$this->dropTable('team_site_ranking');
		$this->dropTable('teams');
		$this->dropTable('teams_facilities');
		$this->dropTable('teams_people');
		$this->dropTable('upload_types');
		$this->dropTable('uploads');
		$this->dropTable('users');
		$this->dropTable('waivers');
		$this->dropTable('waivers_people');
	}
}
