<?php

/**
 * Derived class for implementing functionality for individual signup to team events.
 */
namespace App\Module;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\TableRegistry;
use App\Core\UserCache;
use App\Model\Entity\Event;
use App\Model\Entity\Question;
use App\Model\Entity\Registration;

class EventTypeIndividual extends EventType {
	public function configurationFields() {
		return ['level_of_play'];
	}

	public function configurationFieldsElement() {
		return 'individual';
	}

	public function configurationFieldsRules(EntityInterface $entity) {
		$ret = parent::schedulingFieldsRules($entity);

		$rule = new ExistsIn(['division_id'], 'Divisions');
		if (!$rule($entity, ['errorField' => 'division_id'])) {
			$entity->errors('division_id', ['validDivision' => __('You must select a valid division.')]);
			$ret = false;
		}

		return $ret;
	}

	// ID numbers don't much matter, but they can't be duplicated between event types,
	// and they can't ever be changed, because they're in the database.
	public function registrationFields(Event $event, $user_id, $for_output = false) {
		$fields = [];
		if (Configure::read('profile.shirt_size') == PROFILE_REGISTRATION) {
			$fields = [
				new Question([
					'type' => 'group_start',
					'question' => __('Player Details'),
				]),
				new Question([
					'id' => SHIRT_SIZE,
					'type' => 'select',
					'question' => __('Shirt Size'),
					'empty' => '---',
					'options' => Configure::read('options.shirt_size'),
					'required' => true,
				]),
				new Question([
					'type' => 'group_end',
				]),
			];
		}
		return $fields;
	}

	public function validateResponse($value, $context, Question $question, Array $responses, Event $event, Registration $registration = null) {
		// Some questions are validated based on their ID
		switch ($question->id) {
			case SHIRT_SIZE:
				// TODO: Move this to a more Cake-ish structure
				if (in_array($value, Configure::read('options.shirt_size'))) {
					return true;
				}
				return __('You must select a valid shirt size.');
		}

		return parent::validateResponse($value, $context, $question, $responses, $event, $registration);
	}

	public function beforeRegister(Event $event, Registration $registration, $options) {
		if (Configure::read('profile.shirt_size') == PROFILE_REGISTRATION) {
			$shirt_size = $this->extractAnswers($registration->responses, [
				'shirt_size' => SHIRT_SIZE,
			]);
			if (!empty($shirt_size)) {
				$people_table = TableRegistry::get('People');
				// If it fails, it fails. We're not going to reject the registration because of it.
				$people_table->updateAll($shirt_size, ['id' => UserCache::getInstance()->currentId()]);
			}
		}

		return parent::beforeRegister($event, $registration, $options);
	}

}
