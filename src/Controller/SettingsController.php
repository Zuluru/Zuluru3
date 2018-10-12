<?php
namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'edit',
				])) {
					// If an affiliate id is specified, check if we're a manager of that affiliate
					$affiliate = $this->request->getQuery('affiliate');
					if ($affiliate && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit($section) {
		$affiliate = $this->request->getQuery('affiliate');
		$affiliates = $this->_applicableAffiliates();

		$settings = $this->Settings->find()
			->where([
				'person_id IS' => null,
				'affiliate_id IS' => $affiliate,
			])
			->toArray();

		$defaults = $this->Settings->find()
			->where([
				'person_id IS' => null,
				'affiliate_id IS' => null,
			])
			->indexBy('id')
			->toArray();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$to_delete = [];

			foreach ($this->request->data as $key => $value) {
				if (is_array($value['value'])) {
					// There may be dates that need to be deconstructed
					if ($affiliate && (empty($value['value']['day']) || empty($value['value']['month']))) {
						// If we're editing affiliate settings, anything blank should be removed so the system default applies
						unset($this->request->data[$key]);
						if ($key < MIN_FAKE_ID) {
							$to_delete[] = $key;
						}
					} else if (array_key_exists('year', $value['value'])) {
						$this->request->data[$key]['value'] = sprintf('%04d-%02d-%02d', $value['value']['year'], $value['value']['month'], $value['value']['day']);
					} else if (array_key_exists('month', $value['value'])) {
						$this->request->data[$key]['value'] = sprintf('0-%02d-%02d', $value['value']['month'], $value['value']['day']);
					}
				} else if ($affiliate && ((empty($value['value']) && $value['value'] !== '0') || $value['value'] == MIN_FAKE_ID)) {
					// If we're editing affiliate settings, anything blank should be removed so the system default applies.
					// MIN_FAKE_ID as a value means it was a select or a radio and they chose "use default".
					unset($this->request->data[$key]);
					if ($key < MIN_FAKE_ID) {
						$to_delete[] = $key;
					}
				}
			}

			// Remove old settings that need to be removed, so they don't confuse the display
			$settings = collection($settings)->reject(function ($setting) use ($to_delete) {
				return $setting->has('id') && in_array($setting->id, $to_delete);
			})->toArray();

			$this->Settings->connection()->transactional(function () use ($settings, $to_delete, $affiliate) {
				if (!empty($this->request->data)) {
					$settings = $this->Settings->patchEntities($settings, $this->request->data, ['validate' => false]);
					foreach ($settings as $setting) {
						if (!$this->Settings->save($setting)) {
							$this->Flash->warning(__('Failed to save the settings.'));
							return false;
						}
					}
				}

				if (!empty($to_delete)) {
					if (!$this->Settings->deleteAll(['id IN' => $to_delete])) {
						$this->Flash->warning(__('Failed to save the settings.'));
						return false;
					}
				}

				$this->Flash->success(__('The settings have been saved.'));

				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				if ($affiliate) {
					Cache::delete("config/affiliate/$affiliate", 'long_term');
					$this->Configuration->loadAffiliate($affiliate);
				} else {
					Cache::delete('config', 'long_term');
					$this->Configuration->loadSystem();
				}
				$this->_initMenu();

				return true;
			});
		}

		$this->_loadAddressOptions();

		$this->set(compact('affiliate', 'affiliates', 'settings', 'defaults'));
		$this->render($section);
	}

	public function payment_provider_fields() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$affiliate = $this->request->getQuery('affiliate');
		$settings = $this->Settings->find()
			->where([
				'person_id IS' => null,
				'affiliate_id IS' => $affiliate,
			])
			->toArray();

		$defaults = $this->Settings->find()
			->where([
				'person_id IS' => null,
				'affiliate_id IS' => null,
			])
			->toArray();

		$provider = $this->request->data['payment_implementation'];
		$this->set(compact('affiliate', 'provider', 'settings', 'defaults'));
	}

}
