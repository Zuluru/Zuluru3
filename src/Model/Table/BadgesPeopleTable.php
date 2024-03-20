<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;

/**
 * BadgesPeople Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Badges
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $Registrations
 * @property \Cake\ORM\Association\BelongsTo $NominatedBy
 * @property \Cake\ORM\Association\BelongsTo $ApprovedBy
 */
class BadgesPeopleTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('badges_people');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Badges', [
			'foreignKey' => 'badge_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
		]);
		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
		]);
		$this->belongsTo('Registrations', [
			'foreignKey' => 'registration_id',
		]);
		$this->belongsTo('NominatedBy', [
			'className' => 'People',
			'foreignKey' => 'nominated_by_id',
		]);
		$this->belongsTo('ApprovedBy', [
			'className' => 'People',
			'foreignKey' => 'approved_by_id',
		]);
	}

	public function affiliate($id) {
		try {
			return $this->Badges->affiliate($this->field('badge_id', ['BadgesPeople.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
