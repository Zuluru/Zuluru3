<?php
namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

class ConfigurationLoader {

	/**
	 * @param \Cake\Http\ServerRequest $request The request.
	 * @param \Cake\Http\Response $response The response.
	 * @param callable $next The next middleware to call.
	 * @return \Cake\Http\Response A response.
	 */
	public function __invoke(ServerRequest $request, Response $response, $next) {
		$this->loadConfiguration($request);
		return $next($request, $response);
	}

	public static function loadConfiguration(ServerRequest $request = null) {
		Configure::load('options');

		// Test cases don't have a request object, but need this done anyway.
		if (!$request || $request->getParam('plugin') != 'Installer') {
			// Load configuration from database or cache
			TableRegistry::get('Configuration')->loadSystem();
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
