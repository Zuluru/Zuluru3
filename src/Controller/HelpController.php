<?php
namespace App\Controller;

class HelpController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions(): array {
		return ['view'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['view'];
	}

	public function view($controller = null, $topic = null, $item = null, $subitem = null) {
		$this->set(compact('controller', 'topic', 'item', 'subitem'));
	}

}
