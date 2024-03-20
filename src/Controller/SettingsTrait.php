<?php
namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

trait SettingsTrait {
	/**
	 * Process settings from any source
	 *
	 * @return bool|\Cake\Http\Response Redirects on certain failures, returns indication of whether settings saved otherwise.
	 */
	private function _process($conditions = []) {
		$settings = $this->Settings->find()->where(['person_id IS' => null]);
		if (!empty($conditions)) {
			$settings = $settings->andWhere($conditions);
		}

		$affiliate_id = $this->getRequest()->getQuery('affiliate');
		if ($affiliate_id) {
			try {
				$affiliate = $this->Settings->Affiliates->get($affiliate_id);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid affiliate.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid affiliate.'));
				return $this->redirect('/');
			}
			$settings = $settings->andWhere(['affiliate_id' => $affiliate_id]);
			$this->Authorization->authorize($affiliate, 'edit_settings');
		} else {
			$affiliate = null;
			$settings = $settings->andWhere(['affiliate_id IS' => null]);
			$this->Authorization->authorize(\App\Controller\SettingsController::class, 'edit');
		}

		$settings = $settings->toArray();

		$defaults = $this->Settings->find()
			->where([
				'person_id IS' => null,
				'affiliate_id IS' => null,
			]);
		if (!empty($conditions)) {
			$defaults = $defaults->andWhere($conditions);
		}
		$defaults->indexBy('id')->toArray();
		$this->set(compact('affiliate', 'settings', 'defaults'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();
			$to_delete = [];

			foreach ($data as $key => $value) {
				if (is_array($value['value'])) {
					// There may be dates that need to be deconstructed
					if ($affiliate_id && (empty($value['value']['day']) || empty($value['value']['month']))) {
						// If we're editing affiliate settings, anything blank should be removed so the system default applies
						unset($data[$key]);
						if ($key < MIN_FAKE_ID) {
							$to_delete[] = $key;
						}
					} else if (array_key_exists('year', $value['value'])) {
						$data[$key]['value'] = sprintf('%04d-%02d-%02d', $value['value']['year'], $value['value']['month'], $value['value']['day']);
					} else if (array_key_exists('month', $value['value'])) {
						$data[$key]['value'] = sprintf('0-%02d-%02d', $value['value']['month'], $value['value']['day']);
					}
				} else if ($affiliate_id && ((empty($value['value']) && $value['value'] !== '0') || $value['value'] == MIN_FAKE_ID)) {
					// If we're editing affiliate settings, anything blank should be removed so the system default applies.
					// MIN_FAKE_ID as a value means it was a select or a radio and they chose "use default".
					unset($data[$key]);
					if ($key < MIN_FAKE_ID) {
						$to_delete[] = $key;
					}
				}
			}

			// Remove old settings that need to be removed, so they don't confuse the display
			$settings = collection($settings)->reject(function ($setting) use ($to_delete) {
				return $setting->has('id') && in_array($setting->id, $to_delete);
			})->toArray();

			if ($this->Settings->getConnection()->transactional(function () use ($settings, $to_delete, $affiliate_id) {
				if (!empty($data)) {
					$settings = $this->Settings->patchEntities($settings, $data, ['validate' => false]);
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
				if ($affiliate_id) {
					Cache::delete("config/affiliate/$affiliate_id", 'long_term');
					$this->Configuration->loadAffiliate($affiliate_id);
				} else {
					Cache::delete('config', 'long_term');
					$this->Configuration->loadSystem();
				}
				$this->_initMenu();

				return true;
			})) {
				$this->set(compact('settings'));
				return true;
			}
		}

		return false;
	}

}
