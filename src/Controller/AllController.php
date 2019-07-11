<?php
namespace App\Controller;

use Cake\Cache\Cache;
use Cake\I18n\I18n;

class AllController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['language', 'credits'];
	}

	public function clear_cache() {
		$this->Authorization->authorize($this);

		Cache::clear(false, 'long_term');
		$this->Flash->success(__('The cache has been cleared.'));
		return $this->redirect('/');
	}

	public function language() {
		$lang = $this->request->getQuery('lang');
		if (!empty($lang)) {
			$this->response = $this->response->withCookie('ZuluruLocale', [
				'value' => $lang,
				'path' => '/',
			]);
			if ($this->Authentication->getIdentity()) {
				I18n::setLocale($lang);
				$this->Flash->html(__('Your language has been changed for this session. To change it permanently, {0}.'), [
					'params' => [
						'replacements' => [
							[
								'type' => 'link',
								'link' => __('update your preferences'),
								'target' => ['controller' => 'People', 'action' => 'preferences'],
							],
						],
					],
				]);
			}
		}
		return $this->redirect('/');
	}

	public function credits() {
	}

}
