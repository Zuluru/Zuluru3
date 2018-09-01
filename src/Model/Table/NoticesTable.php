<?php
namespace App\Model\Table;

/**
 * Notices Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class NoticesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('notices');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsToMany('People', [
			'foreignKey' => 'notice_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'notices_people',
			'through' => 'NoticesPeople',
			'saveStrategy' => 'append',
		]);
	}

}
