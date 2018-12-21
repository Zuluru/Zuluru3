<?php
namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

class AffiliateConfigurationLoader {

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
		// Test cases don't have a request object, but need this done anyway.
		if ($request && $request->getParam('plugin') != 'Installer' && $request->getAttribute('identity')) {
			$identity = $request->getAttribute('identity');
			if (Configure::read('feature.affiliates')) {
				$affiliates = $identity->applicableAffiliateIDs();
				if (count($affiliates) == 1) {
					TableRegistry::get('Configuration')->loadAffiliate(current($affiliates));
				}
			}
		}

		Configure::load('sports');
	}
}
