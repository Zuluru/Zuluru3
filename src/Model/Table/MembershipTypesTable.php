<?php
namespace App\Model\Table;

/**
 * MembershipTypes Model
 *
 */
class MembershipTypesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('membership_types');
		$this->setDisplayField('description');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['description']]);
	}

}
