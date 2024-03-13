<?php
namespace App\View\Helper;

use Authorization\AuthorizationServiceInterface;
use Authorization\Exception\ForbiddenException;
use Authorization\IdentityInterface;
use Cake\Utility\Hash;
use Cake\View\Helper;
use RuntimeException;

/**
 * Authorize Helper
 *
 * A convenience helper to access authorization functions of the identity
 */
class AuthorizeHelper extends Helper {
	/**
	 * Identity Object
	 *
	 * @var null|\Authorization\IdentityInterface
	 */
	protected $_identity;

	/**
	 * Authorization Object
	 *
	 * @var null|\Authorization\AuthorizationServiceInterface
	 */
	protected $_authorize;

	/**
	 * Constructor hook method.
	 *
	 * Implement this method to avoid having to overwrite the constructor and call parent.
	 *
	 * @param array $config The configuration settings provided to this helper.
	 * @return void
	 */
	public function initialize(array $config) {
		$this->_identity = $this->getView()->getRequest()->getAttribute('identity');
		$this->_authorize = $this->getView()->getRequest()->getAttribute('authorization');

		if (empty($this->_identity)) {
			return;
		}

		if (!$this->_identity instanceof IdentityInterface) {
			throw new RuntimeException(sprintf('Identity found in request does not implement %s', IdentityInterface::class));
		}
		if (!$this->_authorize instanceof AuthorizationServiceInterface) {
			throw new RuntimeException(sprintf('Authorization service found in request does not implement %s', AuthorizationServiceInterface::class));
		}
	}

	/**
	 * Check whether the current identity can perform an action.
	 *
	 * @param string $action The action/operation being performed.
	 * @param mixed $resource The resource being operated on.
	 * @return bool
	 */
	public function can($action, $resource) {
		if (empty($this->_authorize)) {
			return false;
		}
		if (empty($resource)) {
			throw new \InvalidArgumentException('No resource passed to "can" function.');
		}

		try {
			return $this->_authorize->can($this->_identity, $action, $resource);
		} catch (ForbiddenException $ex) {
			return false;
		}
	}

	/**
	 * Gets user data
	 *
	 * @param string|null $key Key of something you want to get from the identity data
	 * @return mixed
	 */
	public function get($key = null) {
		if (empty($this->_identity)) {
			return null;
		}

		if ($key === null) {
			return $this->_identity->getOriginalData();
		}

		return Hash::get($this->_identity, $key);
	}


	/**
	 * Gets the identity itself
	 *
	 * @param string|null $key Key of something you want to get from the identity data
	 * @return IdentityInterface
	 */
	public function getIdentity() {
		return $this->_identity;
	}

}
