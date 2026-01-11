<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SubRequests Model
 *
 * @property \App\Model\Table\CaptainsTable&\Cake\ORM\Association\BelongsTo $Captains
 * @property \App\Model\Table\TeamsTable&\Cake\ORM\Association\BelongsTo $Teams
 * @property \App\Model\Table\PeopleTable&\Cake\ORM\Association\BelongsTo $People
 *
 * @method \App\Model\Entity\SubRequest newEmptyEntity()
 * @method \App\Model\Entity\SubRequest newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SubRequest[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SubRequest get($primaryKey, $options = [])
 * @method \App\Model\Entity\SubRequest findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SubRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SubRequest[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SubRequest|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubRequest[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubRequest[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubRequest[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubRequest[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SubRequestsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sub_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Captains', [
			'className' => 'People',
            'foreignKey' => 'captain_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Teams', [
            'foreignKey' => 'team_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('People', [
            'foreignKey' => 'person_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->date('game_date')
            ->allowEmptyDate('game_date');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['captain_id'], 'Captains'), ['errorField' => 'captain_id']);
        $rules->add($rules->existsIn(['team_id'], 'Teams'), ['errorField' => 'team_id']);
        $rules->add($rules->existsIn(['person_id'], 'People'), ['errorField' => 'person_id']);

        return $rules;
    }
}
