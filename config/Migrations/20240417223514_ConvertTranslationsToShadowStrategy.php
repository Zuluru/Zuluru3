<?php
declare(strict_types=1);

use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class ConvertTranslationsToShadowStrategy extends AbstractMigration
{
	private $models = [
		'Affiliates',
		'Badges', 'UserGroups', 'Notices', 'Waivers',
		'MembershipTypes', 'RosterRoles', 'StatTypes',
		'Countries', 'Provinces', 'Regions', 'Facilities', 'Fields',
		'Days', 'Leagues', 'Divisions', 'Pools',
		'EventTypes', 'Events', 'Prices', 'Questionnaires', 'Questions', 'Answers',
		'MailingLists', 'Newsletters',
		'Categories', 'Contacts', 'Holidays', 'Tasks', 'UploadTypes',
	];

	/**
	 * Up Method.
	 */
	public function up(): void {
		// Remove the unique name requirement, if it exists
		try {
			$this->table('events')->removeIndex('name')->update();
		} catch (InvalidArgumentException $ex) {
		}

		$defaultLocale = substr(env('APP_DEFAULT_LOCALE', 'en'), 0, 2);
		$i18n = TableRegistry::getTableLocator()->get('I18n');
		$locales = $i18n->find()
			->distinct('locale')
			->select('locale')
			->where(['locale !=' => $defaultLocale])
			->all()
			->extract('locale')
			->toArray();
		array_unshift($locales, $defaultLocale);

		$mapper = function ($record, $key, $mapReduce) {
			$mapReduce->emitIntermediate($record, $record['foreign_key']);
		};
		$reducer = function ($records, $key, $mapReduce) {
			$mapReduce->emit(collection($records)->combine('field', 'content')->toArray(), $key);
		};

		foreach ($this->models as $model) {
			// Get details about the existing table
			$table = TableRegistry::getTableLocator()->get($model);
			$shadow = TableRegistry::getTableLocator()->get("{$model}Translations");
			$fields = $table->getBehavior('Translate')->getConfig('fields');
			$schema = $table->getSchema();
			$tableName = $schema->name();

			// Create the new table
			$newTable = $this->table("{$tableName}_translations", [
				'id' => false, 'primary_key' => ['id', 'locale'], 'collation' => 'utf8mb4_unicode_ci',
			])
				->addColumn('id', 'integer', [
					'default' => null,
					'null' => false,
				])
				->addColumn('locale', 'string', [
					'length' => 6,
					'null' => false,
				]);
			foreach ($fields as $fieldName) {
				$column = $schema->getColumn($fieldName);
				$newTable
					->addColumn($fieldName, $column['type'], [
						'limit' => $column['length'],
						'default' => $column['default'],
						'null' => true,
					]);
			}
			$newTable->create();

			if ($table->hasBehavior('Timestamp')) {
				$table->removeBehavior('Timestamp');
			}

			$table->getEventManager()->off('Model.beforeSave');
			$table->getEventManager()->off('Model.afterSave');
			$table->getEventManager()->off('Model.afterSaveCommit');

			foreach ($locales as $locale) {
				// Load all the existing translations
				$translations = $i18n->find()
					->select(['foreign_key', 'field', 'content'])
					->where(['model' => $model, 'locale' => $locale])
					->disableHydration()
					->mapReduce($mapper, $reducer)
					->toArray();

				if ($locale === $defaultLocale) {
					$defaultTranslations = $translations;

					// Update the primary records with their translations
					$records = $table->find();
					foreach ($records as $record) {
						if (array_key_exists($record->id, $translations)) {
							$record = $table->patchEntity($record, $translations[$record->id]);
							$table->save($record, ['checkRules' => false]);
						}
					}
				} else {
					foreach ($translations as $id => $translation) {
						// Remove any "translations" that match the default locale, it's not a real translation and not useful
						foreach ($translation as $field => $value) {
							if ($value === $defaultTranslations[$id][$field]) {
								unset($translation[$field]);
							}
						}

						// Create shadow table records
						if (!empty($translation)) {
							$record = $shadow->newEntity(compact('id', 'locale') + $translation);
							$shadow->save($record, ['checkRules' => false]);
						}
					}
				}
			}
		}
	}

	/**
	 * Down Method.
	 */
	public function down(): void {
		foreach ($this->models as $model) {
			$table = TableRegistry::getTableLocator()->get($model);
			$schema = $table->getSchema();
			$tableName = $schema->name();

			$this->table("{$tableName}_translations")
				->drop();
		}
	}
}
