<?php
namespace App\Model\Table;

use Cake\Core\Configure;

/**
 * NoticesPeople Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Notices
 * @property \Cake\ORM\Association\BelongsTo $People
 */
class NoticesPeopleTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('notices_people');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Notices', [
			'foreignKey' => 'notice_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);

	}

}
