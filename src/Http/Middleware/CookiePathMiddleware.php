<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Trims trailing slashes off of cookie paths. Systems like Joomla and Drupal
 * may make dashboard URLs that look like "/zuluru", but CakePHP uses the
 * webroot, which always ends in a slash, for the cookie path. This means
 * that standards-compliant browsers (Firefox has a bug in this area) will
 * not send CSRF and Auth cookies to the dashboard, which causes problems.
 */
class CookiePathMiddleware implements MiddlewareInterface {

	/**
	 * @inheritDoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$response = $handler->handle($request);

		$webroot = $request->getAttribute('webroot');
		$newpath = '/' . trim($webroot, '/');
		if ($newpath != $webroot) {
			foreach ($response->getHeaders() as $name => $values) {
				if (strtolower($name) === 'set-cookie') {
					$response = $response->withHeader($name, $this->fixCookies($values, $webroot, $newpath));
				}
			}
		}

		return $response;
	}

	protected function fixCookies(array $cookies, string $oldpath, string $newpath): array {
		foreach ($cookies as $i => $cookie) {
			$cookies[$i] = str_replace("Path=$oldpath", "Path=$newpath", $cookie);
		}
		return $cookies;
	}

}
