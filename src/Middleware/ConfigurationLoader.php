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

		// This happens before the routing middleware has run, so we have to look at the raw URL, not the plugin property.
		if (!$request || strpos($request->getEnv('REQUEST_URI'), '/installer') === false) {
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

		// Ensure login URL is set for authentication (always set to handle database config overwrites)
		// Database config might set App.urls to a scalar value, corrupting the array from app.php
		$urls = Configure::read('App.urls');
		if (!is_array($urls) || !isset($urls['login']) || !is_array($urls['login'])) {
			// App.urls or App.urls.login is missing/corrupted, restore it
			Configure::write('App.urls.login', ['plugin' => false, 'controller' => 'Users', 'action' => 'login']);
		}
	}
}
