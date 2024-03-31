<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Cookie\Cookie;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Replace the third-party cookie setting method with something CakePHP compatible
 */
class LocalizationMiddleware extends \Boronczyk\LocalizationMiddleware {
	public function process(Request $req, RequestHandler $handler): Response
	{
		$locale = $this->getLocale($req);
		$this->localeCallback->__invoke($locale);
		$req = $req->withAttribute($this->reqAttrName, $locale);
		$resp = $handler->handle($req);

		if (in_array(self::FROM_COOKIE, $this->searchOrder)) {
			// The locale may have changed during the serving of the request
			$locale = I18n::getLocale();

			if ($resp instanceof \Cake\Http\Response) {
				$resp = $resp->withCookie(new Cookie(
					$this->cookieName,
					$locale,
					new FrozenTime($this->cookieExpire),
					$this->cookiePath
				));
			} else {
				$resp = $resp->withAddedHeader(
					'Set-Cookie',
					"{$this->cookieName}=$locale; Path={$this->cookiePath}; Expires={$this->cookieExpire}"
				);
			}
		}

		return $resp;
	}
}
