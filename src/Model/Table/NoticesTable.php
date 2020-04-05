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

		$this->setTable('notices');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Translate', ['fields' => ['notice']]);

		$this->belongsToMany('People', [
			'foreignKey' => 'notice_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'notices_people',
			'through' => 'NoticesPeople',
			'saveStrategy' => 'append',
		]);
	}

}
