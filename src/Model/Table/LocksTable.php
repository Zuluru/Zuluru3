<?php
namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * Locks Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 */
class LocksTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('locks');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');
	}

}
