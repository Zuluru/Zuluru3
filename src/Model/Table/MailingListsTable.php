<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\RuleSyntaxRule;
use InvalidArgumentException;

/**
 * MailingLists Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\HasMany $Newsletters
 * @property \Cake\ORM\Association\HasMany $Subscriptions
 */
class MailingListsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('mailing_lists');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name'],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Newsletters', [
			'foreignKey' => 'mailing_list_id',
			'dependent' => false,
		]);
		$this->hasMany('Subscriptions', [
			'foreignKey' => 'mailing_list_id',
			'dependent' => true,
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->boolean('opt_out')
			->requirePresence('opt_out', 'create')
			->notEmptyString('opt_out')

			->allowEmptyString('rule')

			;

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));

		$rules->add(new RuleSyntaxRule(), 'validRule', [
			'errorField' => 'roster_rule',
			'message' => __('There is an error in the rule syntax.'),
		]);

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['MailingLists.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
