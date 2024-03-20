<?php
namespace App\Model\Table;

/**
 * RosterRoles Model
 *
 */
class RosterRolesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('roster_roles');
		$this->setDisplayField('description');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['description']]);
	}

}
