<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @property \Cake\ORM\Association\BelongsTo $User
 */
class DrupalSessionsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table(Configure::read('Security.drupalPrefix') . 'sessions');
		$this->primaryKey('sid');

		$this->belongsTo('User', [
			'className' => 'UserDrupal',
			'foreignKey' => 'uid',
		]);
	}

}
