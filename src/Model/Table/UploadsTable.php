<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\UserCache;
use App\Model\Rule\GreaterDateRule;
use App\Model\Rule\InDateConfigRule;

/**
 * Uploads Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $UploadTypes
 */
class UploadsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('uploads');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		/* TODO: Configuration for S3 storage
		$client = \Aws\S3\S3Client::factory([
			'credentials' => [
				'key'    => 'your-key',
				'secret' => 'your-secret',
			],
			'region' => 'your-region',
			'version' => 'latest',
		]);
		$adapter = new \League\Flysystem\AwsS3v3\AwsS3Adapter(
			$client,
			'your-bucket-name',
			'optional-prefix'
		);

		And add this to the 'filename' portion of the behaviour configuration.
			'filesystem' => [
				'adapter' => $adapter,
			],
		*/
		$this->addBehavior('Josegonzalez/Upload.Upload', [
			'filename' => [
				// Root location in the filesystem for all uploads
				'filesystem' => [
					'root' => Configure::read('App.paths.uploads') . DS,
				],
				// Folder within the root where these files will go; we currently use a flat filesystem.
				'path' => '',
			],
		]);

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('UploadTypes', [
			'foreignKey' => 'type_id',
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

			->requirePresence('filename', 'create', __('There was an unexpected error uploading the file. Please try again.'))
			->notEmptyFile('filename', __('You must select a document to upload.'))
			->add('filename', 'noErrors', [
				'rule' => function ($value, $context) {
					// We may just get a plain filename here, from cropped photo uploads
					if (!is_array($value)) {
						return true;
					}

					// TODO: Use validation functions provided by the Upload behavior
					if ($value['error'] == UPLOAD_ERR_NO_FILE) {
						return __('You must select a document to upload.');
					}
					if ($value['error'] == UPLOAD_ERR_INI_SIZE) {
						$max = ini_get('upload_max_filesize');
						$unit = substr($max,-1);
						if ($unit == 'M' || $unit == 'K') {
							$max .= 'b';
						}
						return __('The selected document is too large. Documents must be less than {0}.', $max);
					}
					if ($value['error'] == UPLOAD_ERR_NO_TMP_DIR || $value['error'] == UPLOAD_ERR_CANT_WRITE) {
						return __('This system does not appear to be properly configured for document uploads. Please contact your administrator to have them correct this.');
					}
					if ($value['error'] == UPLOAD_ERR_PARTIAL) {
						return __('The file was not fully uploaded. Please try again.');
					}
					if ($value['error'] != 0) {
						return __('There was an unexpected error uploading the file. Please try again.');
					}
					if ($value['size'] == 0) {
						return __('You uploaded an empty file. Please try again.');
					}

					return true;
				},
			])

			->boolean('approved')
			->allowEmptyString('approved')

			->date('valid_from', ['ymd'], __('You must provide a valid date.'))
			->allowEmptyDate('valid_from')

			->date('valid_until', ['ymd'], __('You must provide a valid date.'))
			->allowEmptyDate('valid_until')

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
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['type_id'], 'UploadTypes'));

		$rules->addCreate(new InDateConfigRule('event'), 'rangeFromDate', [
			'errorField' => 'valid_from',
			'message' => __('You must provide a valid date.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeUntilDate', [
			'errorField' => 'valid_until',
			'message' => __('You must provide a valid date.'),
		]);

		$rules->add(new GreaterDateRule('valid_from'), 'greaterUntilDate', [
			'errorField' => 'valid_until',
			'message' => __('End date must be after the start date.'),
		]);

		return $rules;
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		UserCache::getInstance()->clear('Documents', $entity->person_id);
	}

	/**
	 * Perform additional operations after the save transaction has been committed.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterDeleteCommit event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterDeleteCommit(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		UserCache::getInstance()->clear('Documents', $entity->person_id);
		unlink(Configure::read('App.paths.uploads') . DS . $entity->filename);
	}

	public function affiliate($id) {
		try {
			return $this->UploadTypes->affiliate($this->field('type_id', ['Uploads.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
