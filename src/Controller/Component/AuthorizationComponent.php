<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\Controller\Component\AuthorizationComponent as CakeAuthorizationComponent;

class AuthorizationComponent extends CakeAuthorizationComponent
{
    public function can($resource, ?string $action = null): bool {
        try {
            return $this->performCheck($resource, $action);
        } catch (ForbiddenException|MissingIdentityException $ex) {
            return false;
        }
    }
}
