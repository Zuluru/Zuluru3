<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Controller\AppController;
use App\Core\UserCache;

/**
 * Form for sending messages
 * @package App\Form
 */
class MessageForm extends Form {

	protected function _buildSchema(Schema $schema) {
		return $schema->addField('contact_id', ['type' => 'int'])
			->addField('subject', ['type' => 'string'])
			->addField('message', ['type' => 'text'])
			->addField('cc', ['type' => 'bool']);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	protected function _buildValidator(Validator $validator) {
		$validator
			->requirePresence('subject', 'create')
			->notEmpty('subject', __('Subject must not be blank.'))

			->requirePresence('message', 'create')
			->notEmpty('message', __('Message must not be blank.'))

			->requirePresence('contact_id', 'create')
			->notEmpty('contact_id', __('You must select a valid contact.'))

			;

		return $validator;
	}

	protected function _execute(array $data) {
		// If this throws an exception, it must be caught by the caller
		$contact = TableRegistry::get('Contacts')->get($data['contact_id']);

		return AppController::_sendMail([
			'to' => $contact,
			'replyTo' => UserCache::getInstance()->read('Person'),
			'cc' => ($data['cc'] ? UserCache::getInstance()->read('Person') : []),
			'subject' => $data['subject'],
			'content' => $data['message'],
			'sendAs' => 'text',
		]);
	}

	public function setError($field, $error) {
		$this->_errors[$field] = $error;
	}
}
