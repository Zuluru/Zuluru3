<?php
/**
 * Simple extension of the core CsrfComponent, which strips slashes from the end of the cookie path.
 */

namespace App\Controller\Component;

use Cake\Controller\Component\CsrfComponent as BaseComponent;
use Cake\Network\Request;
use Cake\Network\Response;

class CsrfComponent extends BaseComponent {
	protected function _setCookie(Request $request, Response $response) {
		$old_webroot = $request->webroot;
		$request->webroot = '/' . trim($request->webroot, '/');
		parent::_setCookie($request, $response);
		$request->webroot = $old_webroot;
	}
}
