<?php

/**
 * Derived class for implementing functionality for membership events.
 */
namespace App\Module;

use Cake\Datasource\EntityInterface;
use App\Model\Entity\Registration;
use App\Model\Rule\GreaterDateRule;
use App\Model\Rule\InConfigRule;
use App\View\Helper\ZuluruTimeHelper;

class EventTypeMembership extends EventType {
	public function configurationFields() {
		return ['membership_begins', 'membership_ends', 'membership_type'];
	}

	public function configurationFieldsElement() {
		return 'membership';
	}

	public function configurationFieldsRules(EntityInterface $entity) {
		$ret = parent::schedulingFieldsRules($entity);

		$rule = new GreaterDateRule('membership_begins');
		if (!$rule($entity, ['errorField' => 'membership_ends'])) {
			$entity->errors('membership_ends', ['validMembershipEnds' => __('The membership ends date must be after the membership begins date.')]);
			$ret = false;
		}

		$rule = new InConfigRule('options.membership_types');
		if (!$rule($entity, ['errorField' => 'membership_type'])) {
			$entity->errors('membership_type', ['validMembershipType' => __('You must select a valid membership type.')]);
			$ret = false;
		}

		return $ret;
	}

	public function longDescription(Registration $registration) {
		return __('{0}: Valid from {1} to {2}', parent::longDescription($registration),
			ZuluruTimeHelper::date($registration->event->membership_begins),
			ZuluruTimeHelper::date($registration->event->membership_ends));
	}

}
