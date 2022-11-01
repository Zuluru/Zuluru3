<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Waiver;
use App\Model\Table\WaiversTable;
use Authorization\IdentityInterface;

class WaiverPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Waiver $waiver) {
		return $identity->isManagerOf($waiver);
	}

	public function canAdd(IdentityInterface $identity, Waiver $waiver) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Waiver $waiver) {
		return $identity->isManagerOf($waiver);
	}

	public function canDelete(IdentityInterface $identity, Waiver $waiver) {
		return $identity->isManagerOf($waiver);
	}

	public function canSign(IdentityInterface $identity, ContextResource $resource) {
		$waiver = $resource->resource();
		if (!$waiver->active) {
			throw new ForbiddenRedirectException(__('Invalid waiver.'));
		}

		// Make sure they're waivering for a valid date
		$date = $resource->date;
		if (!$date || !$waiver->canSign($date) || $resource->valid_from === false) {
			throw new ForbiddenRedirectException(__('Invalid waiver date.'));
		}

		// Check if they have already signed this waiver
		$person = $resource->person;
		if (WaiversTable::signed($person->waivers_people, $date)) {
			throw new ForbiddenRedirectException(__('You have already accepted this waiver.'));
		}

		// Don't allow adults to sign a waiver on behalf of another adult
		if (!$identity->isMe($person) && !AppController::_isChild($person)) {
			throw new ForbiddenRedirectException(__('You are not allowed to accept this waiver on behalf of another person.'));
		}

		return true;
	}

	public function canReview(IdentityInterface $identity, Waiver $waiver) {
		return true;
	}

}
