<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AffiliateConfigurationLoader implements MiddlewareInterface {

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$this->loadConfiguration($request);
		return $handler->handle($request);
	}

	public static function loadConfiguration(ServerRequestInterface $request = null): void {
		if ($request && $request->getParam('plugin') != 'CakePHPAppInstaller' && $request->getAttribute('identity')) {
			$identity = $request->getAttribute('identity');
			if (Configure::read('feature.affiliates')) {
				$affiliates = $identity->applicableAffiliateIDs();
				if (count($affiliates) == 1) {
					TableRegistry::getTableLocator()->get('Configuration')->loadAffiliate(current($affiliates));
				}
			}
		}
	}
}
