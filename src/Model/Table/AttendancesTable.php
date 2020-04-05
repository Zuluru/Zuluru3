<?php
namespace App\Model\Table;

/**
 * Attendances Model
 */
class AttendancesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('attendances');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
		]);
		$this->belongsTo('TeamEvents', [
			'foreignKey' => 'team_event_id',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
	}

}
