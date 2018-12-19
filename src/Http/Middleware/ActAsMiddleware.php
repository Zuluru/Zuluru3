<?php
namespace App\Http\Middleware;

use App\Exception\ForbiddenRedirectException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

/**
 * Handle temporary ActAs requests
 */
class ActAsMiddleware {

	/**
	 * @param \Cake\Http\ServerRequest $request The request.
	 * @param \Cake\Http\Response $response The response.
	 * @param callable $next The next middleware to call.
	 * @return \Cake\Http\Response A response.
	 */
	public function __invoke(ServerRequest $request, Response $response, $next) {
		$identity = $request->getAttribute('identity');

		// Check for a temporary "act as" request.
		$params = $request->getQueryParams();
		if (!empty($params['act_as'])) {
			$act_as = $params['act_as'];
			unset($params['act_as']);
			$request = $request->withQueryParams($params);

			if ($identity) {
				try {
					$target = TableRegistry::get('People')->get($act_as);
					if ($identity->can('act_as', $target)) {
						$identity->actAs($request, $response, $target);
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
					$identity->actAs($request, $response, $user->real_person);
				}
			}

			$request->getSession()->delete('Zuluru.act_as_temporary');
		}

		return $next($request, $response);
	}

}
