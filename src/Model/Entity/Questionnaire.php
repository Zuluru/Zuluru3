<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Validation\Validator;
use App\Module\EventType as EventTypeBase;

/**
 * Questionnaire Entity.
 *
 * @property int $id
 * @property string $name
 * @property bool $active
 * @property int $affiliate_id
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Question[] $questions
 */
class Questionnaire extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	/**
	 * @param Validator $validator
	 * @param EventTypeBase $event_obj The event-type-specific module object
	 * @param array $responses Raw response data
	 * @param Event $event The event entity with further information
	 * @param Registration|null $registration The existing registration entity, if any
	 * @return Validator
	 * TODOLATER: Can this just be moved into the normal Reponses validation? If so, does anything in the Responses rules become obsolete?
	 */
	public function addResponseValidation(Validator $validator, EventTypeBase $event_obj, Array $responses, Event $event, Registration $registration = null) {
		$validator
			->notEmpty('answer_text', __('Must not be blank.'), function ($context) {
				$question = collection($this->questions)->firstMatch(['id' => $context['data']['question_id']]);
				return $question ? (!empty($question->required) || !empty($question->_joinData->required)) : false;
			})
			->add('answer_text', 'valid', [
				'rule' => function ($value, $context) use ($event_obj, $responses, $event, $registration) {
					$question = collection($this->questions)->firstMatch(['id' => $context['data']['question_id']]);
					return $question ? $event_obj->validateResponse($value, $context, $question, $responses, $event, $registration) : false;
				},
			])

			->notEmpty('answer_id', __('Select one.'), function ($context) {
				$question = collection($this->questions)->firstMatch(['id' => $context['data']['question_id']]);
				return $question ? (!empty($question->required) || !empty($question->_joinData->required)) : false;
			})
			->add('answer_id', 'valid', [
				'rule' => function ($value, $context) use ($event_obj, $responses, $event, $registration) {
					$question = collection($this->questions)->firstMatch(['id' => $context['data']['question_id']]);
					if (!$question) {
						return false;
					}

					if ($question->type == 'checkbox') {
						// We accept any checkbox value, unless it's a required checkbox
						if (!empty($question->required) || !empty($question->_joinData->required)) {
							if ($value == 0) {
								return __('This is a required field.');
							}
						}
						return true;
					}

					if (in_array($question->type, ['text', 'textbox'])) {
						return true;
					}

					// For now, any question that comes from the database will accept any answer.
					if ($question->has('answers')) {
						$options = collection($question->answers)->extract('id')->toArray();
						return in_array($value, $options);
					}

					return $event_obj->validateResponse($value, $context, $question, $responses, $event, $registration);
				},
				'message' => __('Select one.'),
			])

			;

		return $validator;
	}

}
