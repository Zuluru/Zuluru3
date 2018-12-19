<?php
namespace App\Policy;

use App\Model\Entity\UserDrupal;
use Authorization\IdentityInterface;

class UserDrupalPolicy extends AppPolicy {

	public function canChange_password(IdentityInterface $identity, UserDrupal $user) {
		return $identity->isManagerOf($user) || $identity->isMe($user) || $identity->isRelative($user);
	}

}
