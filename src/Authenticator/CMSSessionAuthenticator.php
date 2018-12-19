<?php
namespace App\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\PersistenceInterface;
use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Generic base authenticator for any third-party CMS that uses session-based authentication
 */
abstract class CMSSessionAuthenticator extends AbstractAuthenticator implements PersistenceInterface {

	/**
	 * Session authenticator to use
	 *
	 * @var SessionAuthenticator
	 */
	protected $_sessionAuth;

	public function __construct(IdentifierInterface $identifiers, $config) {
		parent::__construct($identifiers, $config);
		$this->_sessionAuth = new SessionAuthenticator($this->getConfig('service')->identifiers());
	}

	/**
	 * {@inheritDoc}
	 */
	public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, $identity) {
		return $this->_sessionAuth->persistIdentity($request, $response, $identity);
	}

	/**
	 * {@inheritDoc}
	 */
	public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response) {
		return $this->_sessionAuth->clearIdentity($request, $response);
	}

}
