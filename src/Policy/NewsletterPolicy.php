<?php
namespace App\Policy;

use App\Model\Entity\Newsletter;
use Authorization\IdentityInterface;

class NewsletterPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManagerOf($newsletter);
	}

	public function canAdd(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManagerOf($newsletter);
	}

	public function canDelete(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManagerOf($newsletter);
	}

	public function canDelivery(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManagerOf($newsletter);
	}

	public function canSend(IdentityInterface $identity, Newsletter $newsletter) {
		return $identity->isManagerOf($newsletter);
	}

}
