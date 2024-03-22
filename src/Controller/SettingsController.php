<?php
namespace App\Controller;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController {

	use SettingsTrait;

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit($section) {
		$result = $this->_process();
		if ($result === true) {
			// For things like language and plugin settings to be recognized, we need to start a new request
			return $this->redirect(['action' => 'edit', '?' => [$section]]);
		} else if (is_object($result)) {
			return $result;
		}

		$this->_loadAddressOptions();
		$this->render($section);
	}

}
