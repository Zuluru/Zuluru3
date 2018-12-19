<?php
namespace App\Http\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;

/**
 * Trims trailing slashes off of cookie paths. Systems like Joomla and Drupal
 * may make dashboard URLs that look like "/zuluru", but CakePHP uses the
 * webroot, which always ends in a slash, for the cookie path. This means
 * that standards-compliant browsers (Firefox has a bug in this area) will
 * not send CSRF and Auth cookies to the dashboard, which causes problems.
 */
class CookiePathMiddleware {

	/**
	 * @param \Cake\Http\ServerRequest $request The request.
	 * @param \Cake\Http\Response $response The response.
	 * @param callable $next The next middleware to call.
	 * @return \Cake\Http\Response A response.
	 */
	public function __invoke(ServerRequest $request, Response $response, $next) {
		$webroot = $request->getAttribute('webroot');
		$newpath = '/' . trim($webroot, '/');
		if ($newpath != $webroot) {
			foreach ($response->getHeaders() as $name => $values) {
				if (strtolower($name) === 'set-cookie') {
					$response = $response->withHeader($name, $this->fixCookies($values, $webroot, $newpath));
				}
			}
		}

		return $next($request, $response);
	}

	protected function fixCookies(array $cookies, $oldpath, $newpath) {
		foreach ($cookies as $i => $cookie) {
			$cookies[$i] = str_replace("Path=$oldpath", "Path=$newpath", $cookie);
		}
		return $cookies;
	}

}
