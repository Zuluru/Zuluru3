<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Custom modifications made for use with CakePHP 3.3.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Http\Middleware;

use Cake\Http\Middleware\CsrfProtectionMiddleware as CakeCsrfProtectionMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;

class CsrfProtectionMiddleware extends CakeCsrfProtectionMiddleware {
	protected function _addTokenCookie($token, ServerRequest $request, Response $response) {
		// This call updates the local request only; the immutable one passed in is unchanged,
		// so we don't need to reset the webroot afterwards.
		$request = $request->withAttribute('webroot', '/' . trim($request->getAttribute('webroot'), '/'));

		return parent::_addTokenCookie($token, $request, $response);
	}
}
