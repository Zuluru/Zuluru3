<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Newsletters Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MailingLists
 * @property \Cake\ORM\Association\HasMany $Deliveries
 */
class NewslettersTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('newsletters');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Timestamp');
		$this->addBehavior('Translate', ['fields' => ['name', 'subject', 'text']]);

		$this->belongsTo('MailingLists', [
			'foreignKey' => 'mailing_list_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Deliveries', [
			'className' => 'ActivityLogs',
			'foreignKey' => 'newsletter_id',
			'dependent' => true,
			'conditions' => ['type' => 'newsletter'],
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

			->requirePresence('subject', 'create')
			->notEmptyString('subject')

			->allowEmptyString('text')

			->date('target')
			->allowEmptyDate('target')

			->email('from_email', false, __('You must supply a valid email address.'))
			->requirePresence('from_email', 'create', __('You must supply a valid email address.'))
			->notEmptyString('from_email', __('You must supply a valid email address.'))

			->email('to_email', false, __('You must supply a valid email address.'))
			->allowEmptyString('to_email')

			->email('reply_to', false, __('You must supply a valid email address.'))
			->allowEmptyString('reply_to')

			->range('delay', [1, 60], __('Delay must be between 1 and 60 minutes.'))
			->requirePresence('delay', 'create', __('Delay must be between 1 and 60 minutes.'))
			->notEmptyString('delay', __('Delay must be between 1 and 60 minutes.'))

			->range('batch_size', [1, 1000], __('Batch size must be between 1 and 1000.'))
			->requirePresence('batch_size', 'create', __('Batch size must be between 1 and 1000.'))
			->notEmptyString('batch_size', __('Batch size must be between 1 and 1000.'))

			->boolean('personalize', __('Indicate whether this newsletter will be personalized.'))
			->requirePresence('personalize', 'create', __('Indicate whether this newsletter will be personalized.'))
			->notEmptyString('personalize', __('Indicate whether this newsletter will be personalized.'))

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
		$rules->add($rules->existsIn(['mailing_list_id'], 'MailingLists', __('You must select a valid mailing list.')));

		return $rules;
	}

	/**
	 * Ensure that any URLs in the HTML are absolute.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		$server = Configure::read('App.fullBaseUrl');
		$data['text'] = strtr($data['text'], [
			'src="/' => "src=\"{$server}/",
			'href="/' => "href=\"{$server}/",
		]);
	}

	public function affiliate($id) {
		try {
			return $this->MailingLists->affiliate($this->field('mailing_list_id', ['Newsletters.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
