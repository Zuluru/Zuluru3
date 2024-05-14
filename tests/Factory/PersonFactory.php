<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
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
	 */
	protected function setDefaultTemplate(): void
	{
		$this->setDefaultData(function(Generator $faker) {
			$gender = $faker->boolean();
			// TODO: Is there a way to force this format in the phoneNumber generator?
			$phoneFormat = '({{areaCode}}) {{exchangeCode}}-####';

			return [
				'first_name' => $faker->firstName(),
				'last_name' => $faker->lastName(),
				'gender' => $gender ? 'Woman' : 'Man',
				'roster_designation' => $gender ? 'Woman' : 'Open',
				'home_phone' => $faker->numerify($faker->parse($phoneFormat)),
				'work_phone' => $faker->boolean() ? $faker->numerify($faker->parse($phoneFormat)) : null,
				'mobile_phone' => $faker->boolean() ? $faker->numerify($faker->parse($phoneFormat)) : null,
				'addr_street' => $faker->streetAddress(),
				'addr_city' => $faker->city(),
				'addr_prov' => 'Ontario',
				'addr_postalcode' => $faker->postcode(),
				'addr_country' => 'Canada',
				'birthdate' => $faker->dateTimeBetween('-60 years', '-18 years'),
				'height' => $faker->numberBetween(48, 80),
				'complete' => true,
				'status' => 'active',

				// The modified field is not nullable and required in the DB
				'modified' => FrozenDate::now(),
			];
		});
	}

	/**
	 * @param int $group_id
	 * @return self
	 */
	public function withGroup(int $group_id): self {
		$group = $this->getTable()->UserGroups->get($group_id);
		return $this->with('UserGroups', $group);
	}

	/**
	 * @param int[] $groups
	 * @return self
	 */
	public function withGroups(array $group_ids): self {
		$groups = [];
		foreach ($group_ids as $group_id) {
			$groups[] = $this->getTable()->UserGroups->get($group_id);
		}
		return $this->with('UserGroups', $groups);
	}

	public function admin(array $data = []): self {
		return $this
			->patchData($data)
			->withGroup(GROUP_ADMIN)
			->with('Users', ['password' => 'test123']);
	}

	public function manager(array $data = []): self {
		return $this
			->patchData($data)
			->withGroup(GROUP_MANAGER)
			->with('Users');
	}

	public function player(array $data = []): self {
		return $this
			->patchData($data)
			->withGroup(GROUP_PLAYER)
			->with('Users');
	}

	public function parent(array $data = []): self {
		return $this
			->patchData($data)
			->withGroup(GROUP_PARENT)
			->with('Users');
	}

	public function child(array $data = []): self {
		// Child records don't have contact details
		$data = array_merge($data, [
			'home_phone' => '',
			'work_phone' => '',
			'mobile_phone' => '',
			'addr_street' => '',
			'addr_city' => '',
			'addr_prov' => '',
			'addr_postalcode' => '',
			'addr_country' => '',
		]);
		return $this
			->patchData($data)
			->withGroup(GROUP_PLAYER);
	}

	public function volunteer(array $data = []): self {
		return $this
			->patchData($data)
			->withGroup(GROUP_VOLUNTEER)
			->with('Users');
	}

}
