<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Client\Message;
use Cake\Http\Response;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to provide and log backward compatibility with old CakePHP-style named URLs
 */
class NamedRoutingMiddleware implements MiddlewareInterface {

	/**
	 * Callable implementation for the middleware stack.
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$named = $this->parseNamedParams($request);
		if (!empty($named)) {
			return (new Response())
				->withLocation(Router::url([
					'plugin' => $request->getParam('plugin'),
					'controller' => $request->getParam('controller'),
					'action' => $request->getParam('action'),
					'_ext' => $request->getParam('_ext'),
					'?' => $named,
				]))
				->withStatus(Message::STATUS_MOVED_PERMANENTLY);
		}

		return $handler->handle($request);
	}

	/**
	 * Adapted from Cake3 core implementation. Doesn't need to handle everything that did, just simple name:value pairs,
	 * not mixed with any other parameters.
	 */
	public static function parseNamedParams(ServerRequestInterface $request, array $options = [])
	{
		$options += ['separator' => ':'];
		$pass = $request->getParam('pass');
		if (!$pass) {
			return [];
		}
		$named = [];
		foreach ((array)$pass as $key => $value) {
			if (strpos($value, $options['separator']) === false) {
				continue;
			}
			unset($pass[$key]);
			list($key, $value) = explode($options['separator'], $value, 2);
			$named[$key] = $value;
		}

		if (!empty($named) && !empty($pass)) {
			throw new \Exception("Unhandled parameter combination in {$request->getUri()}");
		}

		return $named;
	}
}
