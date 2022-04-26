<?php
namespace App\Test\Factory;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class UploadFactory extends BaseFactory
{
	static private $files = [];

	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Uploads';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 * @return void
	 */
	protected function setDefaultTemplate() {
		$this->setDefaultData(function (Generator $faker) {
			return [
				'filename' => $faker->word() . '.png',
				'approved' => true,
				'valid_from' => FrozenDate::now(),
				'valid_until' => FrozenDate::now()->addYear(),
			];
		});
	}

	public function persist() {
		$entity = parent::persist();

		if ($entity->person_id) {
			// Copy the test upload to the destination
			$folder = Configure::read('App.paths.uploads');
			$dummy = TESTS . 'test_app' . DS . 'dummy.png';
			copy($dummy, $folder . DS . $entity->filename);
			self::$files[] = $folder . DS . $entity->filename;
		}

		return $entity;
	}

	public static function cleanup(): void {
		foreach (self::$files as $file) {
			unlink($file);
		}
		self::$files = [];
	}
}
