<?php
namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

class UserPolicy extends AppPolicy {

	public function canChange_password(IdentityInterface $identity, User $user) {
		return $identity->isManagerOf($user) || $identity->isMe($user) || $identity->isRelative($user);
	}

}
