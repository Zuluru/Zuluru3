<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Authentication\ActAsIdentity;
use App\Exception\ForbiddenRedirectException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handle temporary ActAs requests
 */
class ActAsMiddleware implements MiddlewareInterface {

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		/** @var ActAsIdentity $identity */
		$identity = $request->getAttribute('identity');

		// Check for a temporary "act as" request.
		$params = $request->getQueryParams();
		if (!empty($params['act_as'])) {
			$act_as = $params['act_as'];
			unset($params['act_as']);
			$request = $request->withQueryParams($params);

			if ($identity) {
				try {
					$target = TableRegistry::getTableLocator()->get('People')->get($act_as);
					if ($identity->can('act_as', $target)) {
						$identity->actAs($request, $target);
						$request->getSession()->write('Zuluru.act_as_temporary', true);
					} else {
						throw new ForbiddenRedirectException(__('You do not have permission to act as that person.'), '/');
					}
				} catch (RecordNotFoundException $ex) {
				}
			}
		} else if ($request->getPath() == '/' && $request->getSession()->read('Zuluru.act_as_temporary')) {
			// This is the home page, and "act as" was temporary, so reset it.
			if ($identity) {
				$user = $identity->getOriginalData();
				if ($user->real_person) {
					$request->getSession()->write('Zuluru.default_tab_id', $user->person->id);
					$identity->actAs($request, $user->real_person);
				}
			}

			$request->getSession()->delete('Zuluru.act_as_temporary');
		}

		return $handler->handle($request);
	}

}
