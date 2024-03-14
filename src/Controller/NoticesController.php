<?php
namespace App\Controller;

/**
 * Notices Controller
 *
 * @property \App\Model\Table\NoticesTable $Notices
 */
class NoticesController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['viewed'];
	}

	/**
	 * Viewed method
	 *
	 * @return void Generates no output, just updates the database
	 */
	public function viewed($id, $remind = false) {
		$this->disableAutoRender();

		// If the login has timed out, don't try to save
		if (!$this->UserCache->currentId()) {
			return;
		}

		$this->loadModel('NoticesPeople');
		$this->NoticesPeople->save($this->NoticesPeople->newEntity([
			'notice_id' => $id,
			'person_id' => $this->UserCache->currentId(),
			'remind' => $remind,
		]));
	}

}
