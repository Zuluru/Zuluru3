<?php
namespace App\Middleware\UnauthorizedHandler;

use App\Event\FlashTrait;
use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This handler will add a flash message and redirect the response if one of configured exception classes is encountered.
 */
class RedirectFlashHandler extends RedirectHandler {

	// This isn't called anywhere in here, but the exception handlers registered in the
	// handler setup use it.
	use FlashTrait;

	/**
	 * Return a response with a location header set if an exception matches.
	 *
	 * {@inheritDoc}
	 */
	public function handle(Exception $exception, ServerRequestInterface $request, ResponseInterface $response, array $options = []) {
		$options += $this->defaultOptions;

		foreach ($options['exceptions'] as $class => $handler) {
			if (is_numeric($class)) {
				$class = $handler;
				$handler = null;
			}
			if ($exception instanceof $class) {
				if ($handler !== null) {
					return call_user_func($handler, $this, $request, $response, $exception, $options);
				} else {
					$url = $this->getUrl($request, $options);

					return $response
						->withHeader('Location', $url)
						->withStatus($options['statusCode']);
				}
			}
		}

		throw $exception;
	}

	/**
	 * Returns the url for the Location header.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
	 * @param array $options Options.
	 * @param boolean $unauthenticated Indicates whether the requested URL is for unauthenticated or unauthorized access
	 * @return string
	 */
	public function getUrl(ServerRequestInterface $request, array $options) {
		if (isset($options['unauthenticated'])) {
			$url = $options['unauthenticatedUrl'];
		} else {
			$url = $options['unauthorizedUrl'];
		}
		if ($options['referrer'] && $options['queryParam'] !== null && $request->getMethod() === 'GET') {
			$query = urlencode($options['queryParam']) . '=' . urlencode(Router::url($request->getRequestTarget()));
			if (strpos($url, '?') !== false) {
				$query = '&' . $query;
			} else {
				$query = '?' . $query;
			}

			$url .= $query;
		}

		return $url;
	}

}
