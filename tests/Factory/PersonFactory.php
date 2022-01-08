<?php
namespace App\Test\Factory;

use Cake\Chronos\Date;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class PersonFactory extends BaseFactory
{
	protected function initialize(): void
	{
		$this->getTable()
			->hasMany('AffiliatesPeople');
	}

	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'People';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 * @return void
	 */
	protected function setDefaultTemplate()
	{
		$this->setDefaultData(function(Generator $faker) {
			$gender = $faker->boolean();
			return [
				'first_name' => $faker->firstName,
				'last_name' => $faker->lastName,
				'gender' => $gender ? 'Woman' : 'Man',
				'roster_designation' => $gender ? 'Woman' : 'Open',
				'complete' => true,
				'status' => 'active',

				// The modified field is not nullable and required in the DB
				'modified' => Date::now(),
			];
		});
	}

	/**
	 * @param int $group_id
	 * @return self
	 */
	public function withGroup(int $group_id): self {
		$group = $this->getTable()->Groups->get($group_id);
		return $this->with('Groups', $group);
	}

	/**
	 * @param int[] $groups
	 * @return self
	 */
	public function withGroups(array $group_ids): self {
		$groups = [];
		foreach ($group_ids as $group_id) {
			$groups[] = $this->getTable()->Groups->get($group_id);
		}
		return $this->with('Groups', $groups);
	}

	/**
	 * @param array|callable|null|int|\Cake\Datasource\EntityInterface|\Cake\Datasource\EntityInterface[] $makeParameter Injected data
	 * @param int $times Number of entities created
	 * @return static
	 */
	public static function makeAdmin($makeParameter = [], int $times = 1): self {
		return self::make($makeParameter, $times)
			->withGroup(GROUP_ADMIN)
			->with('Users');
	}

	/**
	 * @param array|callable|null|int|\Cake\Datasource\EntityInterface|\Cake\Datasource\EntityInterface[] $makeParameter Injected data
	 * @param int $times Number of entities created
	 * @return static
	 */
	public static function makeManager($makeParameter = [], int $times = 1): self {
		return self::make($makeParameter, $times)
			->withGroup(GROUP_MANAGER)
			->with('Users');
	}

	/**
	 * @param array|callable|null|int|\Cake\Datasource\EntityInterface|\Cake\Datasource\EntityInterface[] $makeParameter Injected data
	 * @param int $times Number of entities created
	 * @return static
	 */
	public static function makePlayer($makeParameter = [], int $times = 1): self {
		return self::make($makeParameter, $times)
			->withGroup(GROUP_PLAYER)
			->with('Users');
	}

	/**
	 * @param array|callable|null|int|\Cake\Datasource\EntityInterface|\Cake\Datasource\EntityInterface[] $makeParameter Injected data
	 * @param int $times Number of entities created
	 * @return static
	 */
	public static function makeParent($makeParameter = [], int $times = 1): self {
		return self::make($makeParameter, $times)
			->withGroup(GROUP_PARENT)
			->with('Users');
	}

	/**
	 * @param array|callable|null|int|\Cake\Datasource\EntityInterface|\Cake\Datasource\EntityInterface[] $makeParameter Injected data
	 * @param int $times Number of entities created
	 * @return static
	 */
	public static function makeVolunteer($makeParameter = [], int $times = 1): self {
		return self::make($makeParameter, $times)
			->withGroup(GROUP_VOLUNTEER)
			->with('Users');
	}

}
