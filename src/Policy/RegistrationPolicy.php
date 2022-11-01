<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Registration;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class RegistrationPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canStatistics(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canReport(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAccounting(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Registration $registration) {
		return $identity->isManagerOf($registration);
	}

	public function canRegister_payment_fields(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canRedeem(IdentityInterface $identity, ContextResource $resource) {
		$registration = $resource->resource();

		if (!$identity->isMe($registration) && !$identity->isRelative($registration)) {
			return false;
		}

		// Check whether we can even add a payment to this
		$unpaid = in_array($registration->payment, Configure::read('registration_unpaid')) && $registration->total_amount - $registration->total_payment > 0;
		$unaccounted = $registration->payment === 'Paid' && $registration->total_payment != $registration->total_amount;
		if (!$unpaid && !$unaccounted) {
			throw new ForbiddenRedirectException(__('This registration is marked as {0}.', __($registration->payment)),
				['action' => 'checkout']);
		}
		if ($registration->balance <= 0) {
			throw new ForbiddenRedirectException(__('This registration is already paid in full.'),
				['action' => 'checkout']);
		}

		// Check that we're still allowed to pay for this
		$price = $resource->price;
		if (!$price->allow_late_payment && $price->close->isPast()) {
			$other_prices = collection($resource->prices)->filter(function ($price) {
				return $price->close->isFuture();
			});
			if ($other_prices->isEmpty()) {
				throw new ForbiddenRedirectException(__('The payment deadline has passed.'),
					['action' => 'checkout']);
			} else {
				throw new ForbiddenRedirectException(__('The payment deadline has passed. Please choose another payment option.'),
					['action' => 'edit', 'registration' => $registration->id]);
			}
		}

		// Find the registration cap and how many are already registered.
		$event = $resource->event;
		$person = $resource->person;
		$cap = $event->cap($person->roster_designation);
		if ($cap != CAP_UNLIMITED) {
			$paid = $event->count($person->roster_designation, ['Registrations.id !=' => $registration->id]);
			if ($cap <= $paid || $registration->payment === 'Waiting') {
				throw new ForbiddenRedirectException(__('You are on the waiting list for this event.'),
					['action' => 'checkout']);
			}
		}

		if (empty($person->credits)) {
			throw new ForbiddenRedirectException(__('You have no available credits.'),
				['action' => 'checkout']);
		}

		return true;
	}

	public function canCheckout(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canUnregister(IdentityInterface $identity, Registration $registration) {
		if (!$identity->isManagerOf($registration) && !$identity->isMe($registration) && !$identity->isRelative($registration)) {
			return false;
		}

		if (in_array($registration->payment, Configure::read('registration_some_paid')) && $registration->total_amount > 0) {
			throw new ForbiddenRedirectException(__('You have already paid for this! Contact the office to arrange a refund.'),
				['action' => 'checkout']);
		}
		if (in_array($registration->payment, Configure::read('registration_cancelled'))) {
			throw new ForbiddenRedirectException(__('This registration has already been cancelled. Cancelled records are kept on file for accounting purposes.'),
				['action' => 'checkout']);
		}

		return true;
	}

	public function canAdd_payment(IdentityInterface $identity, Registration $registration) {
		if (!$identity->isManagerOf($registration)) {
			return false;
		}

		// Check whether we can even add a payment to this
		$unpaid = in_array($registration->payment, Configure::read('registration_unpaid')) && $registration->total_amount - $registration->total_payment > 0;
		$unaccounted = $registration->payment === 'Paid' && $registration->total_payment != $registration->total_amount;
		if (!$unpaid && !$unaccounted) {
			throw new ForbiddenRedirectException(__('This registration is marked as {0}.', __($registration->payment)),
				['action' => 'view', 'registration' => $registration->id]);
		}
		if ($registration->balance <= 0) {
			throw new ForbiddenRedirectException(__('This registration is already paid in full; you may need to edit it manually to mark it as paid.'),
				['action' => 'view', 'registration' => $registration->id]);
		}
		if ($registration->payment === 'Waiting') {
			throw new ForbiddenRedirectException(__('Payments cannot be added for registrations on the waiting list.'),
				['action' => 'view', 'registration' => $registration->id]);
		}

		return true;
	}

	public function canInvoice(IdentityInterface $identity, Registration $registration) {
		return $identity->isManagerOf($registration) || $identity->isMe($registration) || $identity->isRelative($registration);
	}

	public function canEdit(IdentityInterface $identity, Registration $registration) {
		if ($identity->isManagerOf($registration)) {
			return true;
		}

		if ($identity->isMe($registration) || $identity->isRelative($registration)) {
			// TODO: Allow people to edit their responses.
			if (!in_array($registration->payment, Configure::read('registration_none_paid')) && $registration->total_amount > 0) {
				throw new ForbiddenRedirectException(__('You cannot edit a registration once a payment has been made.'));
			}

			return true;
		}

		return false;
	}

	public function canUnpaid(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

}
