<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Model\Entity\MailingList;
use App\PasswordHasher\HasherTrait;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;

class MailingListPolicy extends AppPolicy {

	use HasherTrait;

	public function before($identity, $resource, $action) {
		$result = $this->blockAnonymousExcept($identity, $action, ['unsubscribe']);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}

		$result = $this->blockLocked($identity);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, MailingList $mailing_list) {
		return $identity->isManagerOf($mailing_list);
	}

	public function canPreview(IdentityInterface $identity, MailingList $mailing_list) {
		return $identity->isManagerOf($mailing_list);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, MailingList $mailing_list) {
		return $identity->isManagerOf($mailing_list);
	}

	public function canDelete(IdentityInterface $identity, MailingList $mailing_list) {
		return $identity->isManagerOf($mailing_list);
	}

	public function canUnsubscribe(IdentityInterface $identity = null, ContextResource $resource) {
		// Authenticate the hash code
		if ($resource->has('code')) {
			if (!$resource->has('person_id')) {
				return new MissingIdentityResult();
			}

			$code = $resource->code;
			if (!$this->_checkHash([$resource->person_id, $resource->resource()->id], $code)) {
				return new RedirectResult(__('The authorization code is invalid.'));
			}

			return true;
		}

		// If there wasn't a code, then anyone not logged in cannot proceed
		if (!$identity) {
			return new MissingIdentityResult();
		}

		return true;
	}

}
