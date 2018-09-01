<?php
/**
 * Implementation of registration event listeners.
 */

namespace App\Event;

use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventListenerInterface;
use App\Controller\AppController;

class RegistrationListener implements EventListenerInterface {

	public function implementedEvents() {
		return [
			'Model.Registration.registrationRemoved' => 'registrationRemoved',
			'Model.Registration.registrationWaitlisted' => 'registrationWaitlisted',
			'Model.Registration.registrationOpened' => 'registrationOpened',
		];
	}

	public function registrationRemoved(CakeEvent $cakeEvent, $registration) {
		AppController::_sendMail([
			'to' => $registration->person,
			'subject' => __('{0} Registration removed', Configure::read('organization.name')),
			'template' => 'registration_removed',
			'sendAs' => 'both',
			'viewVars' => [
				'registration' => $registration,
				'event' => $registration->event,
				'person' => $registration->person,
			],
		]);
	}

	public function registrationWaitlisted(CakeEvent $cakeEvent, $registration) {
		AppController::_sendMail([
			'to' => $registration->person,
			'subject' => __('{0} Registration moved to waiting list', Configure::read('organization.name')),
			'template' => 'registration_waiting',
			'sendAs' => 'both',
			'viewVars' => [
				'registration' => $registration,
				'event' => $registration->event,
				'person' => $registration->person,
			],
		]);
	}

	public function registrationOpened(CakeEvent $cakeEvent, $registration) {
		AppController::_sendMail([
			'to' => $registration->person,
			'subject' => __('{0} Waiting list opening', Configure::read('organization.name')),
			'template' => 'registration_opening',
			'sendAs' => 'both',
			'viewVars' => [
				'registration' => $registration,
				'event' => $registration->event,
				'person' => $registration->person,
			],
		]);
	}

}