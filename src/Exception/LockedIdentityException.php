<?php
namespace App\Exception;

use Authorization\Exception\ForbiddenException;

/**
 * Used when an identity is denied access due to being locked.
 */
class LockedIdentityException extends ForbiddenException {
	// No actual implementation details here. We just use the class name in the unauthorized handler configuration.
}
