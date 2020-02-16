<?php

use Cake\I18n\I18n;
use Migrations\AbstractSeed;

/**
 * I18n seed.
 */
class I18nSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('i18n');

		// Generate i18n table entries for all known locales
		foreach (['ar', 'de', 'es', 'fr'] as $locale) {
			I18n::setLocale($locale);

			// Generate entries for all relevant tables
			foreach ([
				'Affiliates', 'Countries', 'EventTypes', 'Provinces', 'Regions',
				'Badges' => ['name', 'description'],
				'Days' => ['name', 'short_name'],
				'Groups' => ['name', 'description'],
				'MembershipTypes' => ['description'],
				'Notices' => ['notice'],
				'RosterRoles' => ['description'],
				'StatTypes' => ['name', 'abbr'],
				'Waivers' => ['name', 'description'],
			] as $model => $fields) {
				if (is_numeric($model)) {
					$model = $fields;
					$fields = ['name'];
				}

				// Get the data from the tables's seed
				$seed_class = "{$model}Seed";
				$seed = new $seed_class;
				$seed_data = $seed->data();

				$id = 1;
				$data = [];
				foreach ($seed_data as $record) {
					// Some seeds have id fields included
					if (array_key_exists('id', $record)) {
						$id = $record['id'];
					}

					foreach ($fields as $field) {
						$data[] = [
							'locale' => $locale,
							'model' => $model,
							'foreign_key' => $id,
							'field' => $field,
							'content' => __d('seeds', $record[$field]),
						];
					}

					++ $id;
				}

				$table->insert($data)->save();
			}

			// Settings data is handled separately; only a few need translation
			$data = [
				[
					'locale' => $locale,
					'model' => 'Settings',
					'foreign_key' => 1,
					'field' => 'value',
					'content' => __d('seeds', 'Club'),
				],
				[
					'locale' => $locale,
					'model' => 'Settings',
					'foreign_key' => 2,
					'field' => 'value',
					'content' => __d('seeds', 'Club'),
				],
				// TODO: Add translation of default payment options, refund policy, etc.
			];

			$table->insert($data)->save();
		}

		// Reset the locale, so that any other seeds aren't affected
		I18n::setLocale('en');
	}
}
