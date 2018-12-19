<?php
use Cake\Core\Configure;
use App\Controller\AppController;

$fp = fopen('php://output','w+');
$is_manager = $this->Authorize->getIdentity()->isManagerOf($event);

$fields = [
	__('User ID') => true,
	'first_name' => __('First Name'),
	'last_name' => __('Last Name'),
	'email' => __('Email Address'),
	'alternate_email' => __('Alternate Email Address'),
	'addr_street' => __('Address'),
	'addr_city' => __('City'),
	'addr_prov' => __('Province'),
	'addr_postalcode' => __('Postal Code'),
];

if ($is_manager) {
	$fields += [
		'home_phone' => __('Home Phone'),
		'work_phone' => __('Work Phone'),
		'work_ext' => __('Work Ext'),
		'mobile_phone' => __('Mobile Phone'),
	];
}

$fields += [
	Configure::read('gender.column') => Configure::read('gender.label'),
	'birthdate' => __('Birthdate'),
	'height' => __('Height'),
	'skill_level' => ['name' => __('Skill Level'), 'model' => 'skills'],
	'shirt_size' => __('Shirt Size'),
	'alternate_first_name' => __('Alternate First Name'),
	'alternate_last_name' => __('Alternate Last Name'),
];

if ($is_manager) {
	$fields += [
		'alternate_work_phone' => __('Alternate Work Phone'),
		'alternate_work_ext' => __('Alternate Work Ext'),
		'alternate_mobile_phone' => __('Alternate Mobile Phone'),
	];
}

$fields += [
	__('Order ID') => true,
	__('Created Date') => true,
	__('Modified Date') => true,
	__('Payment Status') => true,
];

if ($is_manager) {
	$fields += [
		__('Total Amount') => true,
		__('Amount Paid') => true,
		__('Price Point') => count($event->prices) > 1,
		__('Transaction ID') => Configure::read('registration.online_payments') ? true : false,
		__('Notes') => true,
	];
}

foreach ($event->questionnaire->questions as $question) {
	if (in_array($question->type, ['text', 'textarea', 'radio', 'select'])) {
		if ($question->has('name')) {
			$fields[$question->name] = true;
		} else {
			$fields[$question->question] = true;
		}
	} else if ($question->type == 'checkbox') {
		if (!empty($question->answers)) {
			foreach ($question->answers as $answer) {
				$fields[$answer->answer] = true;
			}
		} else {
			$fields[$question->question] = true;
		}
	}
}

list($header1, $header2, $player_fields, $contact_fields) = \App\Lib\csvFields($registrations->extract('person'), $fields, $is_manager);
if (!empty($header1)) {
	fputcsv($fp, $header1);
}
fputcsv($fp, $header2);

$order_id_format = Configure::read('registration.order_id_format');

foreach ($registrations as $registration) {
	$row = [$registration->person->id];
	foreach ($player_fields as $field => $name) {
		if (is_array($name)) {
			$model = $name['model'];
			if (is_array($registration->person->$model) && array_key_exists(0, $registration->person->$model)) {
				$row[] = $registration->person->{$model}[0]->$field;
			} else if (is_a($registration->person->$model, 'Cake\ORM\Entity') && $registration->person->$model->has($field)) {
				$row[] = $registration->person->$model->$field;
			} else {
				$row[] = '';
			}
		} else {
			if ($registration->person->has($field)) {
				$row[] = $registration->person->$field;
			} else {
				$row[] = '';
			}
		}
	}
	$row[] = sprintf($order_id_format, $registration->id);
	$row[] = $registration->created;
	$row[] = $registration->modified;
	$row[] = $registration->payment;
	$row[] = $registration->total_amount;
	$row[] = $registration->total_payment;
	if (count($event->prices) > 1) {
		$row[] = $event->prices[$registration->price_id]->name;
	}
	if (Configure::read('registration.online_payments')) {
		$row[] = implode(';', array_unique(collection($registration->payments)->extract('registration_audit.transaction_id')->toArray()));
	}
	$row[] = $registration->notes;
	foreach ($event->questionnaire->questions as $question) {
		if (in_array($question->type, ['text', 'textarea', 'radio', 'select'])) {
			$response = collection($registration->responses)->firstMatch(['question_id' => $question->id]);
			if ($response) {
				if (!empty($response->answer_id)) {
					if (empty($question->answers)) {
						$row[] = $response->answer_id;
					} else {
						$answer = collection($question->answers)->firstMatch(['id' => $response->answer_id]);
						$row[] = $answer->answer;
					}
				} else {
					$row[] = $response->answer_text;
				}
			} else {
				$row[] = '';
			}
		} else if ($question->type == 'checkbox') {
			if (!empty($question->answers)) {
				foreach ($question->answers as $answer) {
					$answers = collection($registration->responses)->match(['question_id' => $question->id, 'answer_id' => $answer->id]);
					$row[] = $answers->isEmpty() ? __('No') : __('Yes');
				}
			} else {
				// Auto questions may fall into this category
				$answers = collection($registration->responses)->match(['question_id' => $question->id, 'answer_id' => 1]);
				$row[] = $answers->isEmpty() ? __('No') : __('Yes');
			}
		}
	}

	if (!empty($contact_fields) && (empty($registration->person->user_id) || AppController::_isChild($registration->person))) {
		foreach ($registration->person->related as $i => $relative) {
			foreach (array_keys($contact_fields[$i]) as $field) {
				if ($relative->has($field)) {
					$row[] = $relative->$field;
				} else {
					$row[] = '';
				}
			}
		}
	}

	fputcsv($fp, $row);
}

fclose($fp);
