<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Entity\Day;

/**
 * Days Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $Divisions
 */
class DaysTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('days');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsToMany('Divisions', [
			'foreignKey' => 'day_id',
			'targetForeignKey' => 'division_id',
			'joinTable' => 'divisions_days',
			'saveStrategy' => 'replace',
		]);
	}
}
