<?php
namespace App\Policy;

use App\Model\Entity\UserJoomla;
use Authorization\IdentityInterface;

class UserJoomlaPolicy extends AppPolicy {

	public function canChange_password(IdentityInterface $identity, UserJoomla $user) {
		return $identity->isManagerOf($user) || $identity->isMe($user) || $identity->isRelative($user);
	}

}
