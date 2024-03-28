<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ConfigurationLoader implements MiddlewareInterface {

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$this->loadConfiguration($request);
		return $handler->handle($request);
	}

	public static function loadConfiguration(ServerRequestInterface $request = null): void {
		Configure::load('options');

		// Test cases don't have a request object, but need this done anyway.
		// This happens before the routing middleware has run, so we have to look at the raw URL, not the plugin property.
		if (!Configure::read('Installer')) {
			// Load configuration from database or cache
			TableRegistry::getTableLocator()->get('Configuration')->loadSystem();
		}

		Configure::load('sports');

		if (empty(Configure::read('Security.authenticators'))) {
			// Default values for when Zuluru is running the show; some of this is
			// needed early on, so take care of all of it here
			Configure::write('feature.control_account_creation', true);
			Configure::write('feature.authenticate_through', 'Zuluru');
		}
	}
}
