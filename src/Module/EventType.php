<?php
/**
 * Base class for event-specific functionality.  This class defines default
 * no-op functions for all operations that events might need to do, as well
 * as providing some common utility functions that derived classes need.
 */
namespace App\Module;

use App\Exception\ForbiddenRedirectException;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use App\Core\UserCache;
use App\Core\ModuleRegistry;
use App\Model\Entity\Event;
use App\Model\Entity\Question;
use App\Model\Entity\Registration;
use Cake\Routing\Router;

class EventType {

	/**
	 * Return the list of field names used for configuration.
	 *
	 */
	public function configurationFields() {
		return [];
	}

	/**
	 * Return the name of the element used to render configuration fields.
	 *
	 */
	public function configurationFieldsElement() {
		return 'none';
	}

	/**
	 * Check integrity of event-type-specific data.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be checked.
	 * @return bool
	 */
	public function configurationFieldsRules(EntityInterface $entity) {
		return true;
	}

	/**
	 * Return an array of registration fields in questionnaire format.
	 *
	 */
	public function registrationFields(Event $event, $user_id, $for_output = false) {
		return [];
	}

	/**
	 * Perform validation on a value.
	 * By default, event types require no additional validation.
	 *
	 * @param mixed $value The value to check
	 * @param array $context The context passed to the validation function
	 * @param Question $question The question entity to check it against
	 * @param array $responses Raw response data
	 * @param Event $event The event entity with further information
	 * @param Registration|null $registration The existing registration entity, if any
	 * @return mixed true if validation succeeds, false or a specific message string if it fails
	 */
	public function validateResponse($value, $context, Question $question, array $responses, Event $event, Registration $registration = null) {
		// Others are validated based on their type
		switch ($question->type) {
			case 'text':
			case 'textarea':
				return !$question->_joinData->required || !empty($value);
		}

		return true;
	}

	public function beforeRegister(Event $event, Registration $registration, $options) {
		return true;
	}

	public function afterRegister(Event $event, Registration $registration, $options) {
	}

	public function beforeUnregister(Event $event, Registration $registration, $options) {
		return true;
	}

	public function afterUnregister(Event $event, Registration $registration, $options) {
		// Check if anything else must be removed as a result (e.g. team reg after removing membership)
		while (empty($options['from_unregister_dependencies']) && $this->unregisterDependencies($registration)) {
		}
	}

	private function unregisterDependencies($registration) {
		// Clear the cache, or else any deletions we do don't have any effect below...
		UserCache::getInstance()->_deleteRegistrationData($registration->person_id);

		// Get everything from the user record that the decisions below might need
		$cache = UserCache::getInstance();
		$person = $cache->read('Person', $registration->person_id);
		$person->group_ids = $cache->read('UserGroupIDs', $registration->person_id);
		$person->teams = $cache->read('AllTeams', $registration->person_id);
		$person->preregistrations = $cache->read('Preregistrations', $registration->person_id);
		$person->registrations = array_merge(
			$cache->read('RegistrationsPaid', $registration->person_id),
			$cache->read('RegistrationsUnpaid', $registration->person_id)
		);
		$person->uploads = $cache->read('Documents', $registration->person_id);
		$person->affiliates = $cache->read('Affiliates', $registration->person_id);
		$person->waivers = $cache->read('Waivers', $registration->person_id);

		$unregistered = false;

		// Pull out the list of unpaid registrations; these are the ones that might be removed
		$unpaid = collection($person->registrations)->filter(function ($r) use ($registration) {
			return $r->id != $registration->id && in_array($r->payment, Configure::read('registration_none_paid'));
		});

		$rule_obj = ModuleRegistry::getInstance()->load('RuleEngine');
		foreach ($unpaid as $key => $registration) {
			$person->registrations = collection($person->registrations)->reject(function ($r) use ($registration) {
				return $r->id == $registration->id;
			})->toArray();

			// Check the registration rule, if any
			$can_register = false;
			foreach ($registration->event->prices as $price) {
				if ($price->close->isFuture() &&
					(empty($price->register_rule) ||
						($rule_obj->init($price->register_rule) && $rule_obj->evaluate($registration->event->affiliate_id, $person))
					)
				) {
					$can_register = true;
					break;
				}
			}

			if (!$can_register) {
				TableRegistry::getTableLocator()->get('Registrations')->delete($registration, ['from_unregister_dependencies' => true]);
				$unregistered = true;

				// Refresh the list
				$person->registrations = array_merge(
					$cache->read('RegistrationsPaid', $registration->person_id),
					$cache->read('RegistrationsUnpaid', $registration->person_id)
				);
			}
		}

		return $unregistered;
	}

	public function beforeReserve(Event $event, Registration $registration, $options) {
		return true;
	}

	public function afterReserve(Event $event, Registration $registration, $options) {
		// There might be unpaid registrations now to be moved to the waiting list
		if (empty($options['from_waiting_list'])) {
			$event->processWaitingList();
		}
	}

	public function beforeUnreserve(Event $event, Registration $registration, $options) {
		// Default payment status to change the unreserved registration to. This may be updated later by processWaitingList.
		// TODOTESTING: Can this bit go away? Is the payment status set correctly everywhere that might call this?
		if (empty($options['from_expire_reservations']) && empty($options['from_unregister_dependencies']) && $registration->payment != 'Cancelled' && $registration->payment != 'Unpaid' && $registration->payment != 'Reserved' && $registration->total_amount > 0) {
			\Cake\Log\Log::write('error', (string)$registration);
			throw new ForbiddenRedirectException('This registration is not marked as unpaid. There is an unresolved issue around this. Details have been logged to assist with correcting it.',
				['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]], 'error');
		}

		return true;
	}

	public function afterUnreserve(Event $event, Registration $registration, $options) {
		// There might be waiting registrations now to be moved to the reserved list
		if (empty($options['from_waiting_list'])) {
			$event->processWaitingList($registration->id);
		}
	}

	public function beforePaid(Event $event, Registration $registration, $options) {
		if (Configure::read('feature.badges')) {
			$badge_obj = ModuleRegistry::getInstance()->load('Badge');
			if (!$badge_obj->update('registration', $registration, true)) {
				Router::getRequest()->getFlash()->warning(__('Failed to update badge information!'));
				return false;
			}
		}

		// Delete any preregistration that might exist for this. We don't do it until the time of payment,
		// because otherwise things like the checkout page might not let the payment proceed.
		TableRegistry::getTableLocator()->get('Preregistrations')->deleteAll(['event_id' => $event->id, 'person_id' => $registration->person_id]);

		return true;
	}

	public function afterPaid(Event $event, Registration $registration, $options) {
	}

	public function beforeUnpaid(Event $event, Registration $registration, $options) {
		if (Configure::read('feature.badges')) {
			$badge_obj = ModuleRegistry::getInstance()->load('Badge');
			if (!$badge_obj->update('registration', $registration, false)) {
				Router::getRequest()->getFlash()->warning(__('Failed to update badge information!'));
				return false;
			}
		}

		return true;
	}

	public function afterUnpaid(Event $event, Registration $registration, $options) {
	}

	public function beforeReregister(Event $event, Registration $registration, $options) {
		return true;
	}

	public function afterReregister(Event $event, Registration $registration, $options) {
	}

	public function longDescription(Registration $registration) {
		return $registration->long_description;
	}

	public static function extractAnswer($data, $question) {
		$answer = collection($data)->firstMatch(['question_id' => $question]);
		if ($answer) {
			// This code needs to handle both arrays and entities.
			if (is_object($answer) && $answer->answer_id !== null) {
				return $answer->answer_id;
			} else if (is_array($answer) && array_key_exists('answer_id', $answer) && $answer['answer_id'] !== null) {
				return $answer['answer_id'];
			} else {
				// Although we have the Trim behaviour on the Responses table, the $data array that is passed
				// here has NOT been run through the marshaller. This is the problem with having validation of
				// one response depend on another response. :-(
				return trim($answer['answer_text']);
			}
		} else {
			return null;
		}
	}

	public static function extractAnswers($data, $questions) {
		$answers = [];
		foreach ($questions as $field => $question) {
			$answer = self::extractAnswer($data, $question);
			if (!empty($answer) || $answer === 0) {
				$answers[$field] = $answer;
			}
		}
		return $answers;
	}

}
