<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;

/**
 * Facilities Controller
 *
 * @property \App\Model\Table\FacilitiesTable $Facilities
 */
class FacilitiesController extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		if (Configure::read('Perm.is_manager')) {
			// If a facility id is specified, check if we're a manager of that facility's affiliate
			$facility = $this->request->query('facility');
			if ($facility) {
				if (!in_array($this->Facilities->affiliate($facility), $this->UserCache->read('ManagedAffiliateIDs'))) {
					Configure::write('Perm.is_manager', false);
				}
			}
		}

		return ['index', 'view'];
	}

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
				// Managers can perform these operations
				if (in_array($this->request->params['action'], [
					'add',
					'add_field',
					'closed',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'edit',
					'open',
					'close',
					'delete',
				])) {
					// If a facility id is specified, check if we're a manager of that facility's affiliate
					$facility = $this->request->query('facility');
					if ($facility) {
						if (in_array($this->Facilities->affiliate($facility), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		$this->Security->config('unlockedActions', ['add', 'edit']);
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$affiliates = $this->_applicableAffiliateIDs();

		$regions = $this->Facilities->Regions->find()
			->contain([
				'Facilities' => [
					'queryBuilder' => function (Query $q) {
						return $q->find('open')
							->order(['Facilities.name']);
					},
					'Fields' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Fields.is_open' => true])
								->order(['Fields.num']);
						},
					],
				],
				'Affiliates',
			])
			->where(['Regions.affiliate_id IN' => $affiliates])
			->order(['Regions.id'])
			->toArray();

		$this->set('closed', false);
		$this->set(compact('affiliates', 'regions'));
		$this->set('_serialize', true);
	}

	public function closed() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$regions = $this->Facilities->Regions->find()
			->contain([
				'Facilities' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['Facilities.name']);
					},
					'Fields' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Fields.is_open' => false])
								->order(['Fields.num']);
						},
					],
				],
				'Affiliates',
			])
			->where(['Regions.affiliate_id IN' => $affiliates])
			->order(['Regions.id'])
			->toArray();

		$this->set('closed', true);
		$this->set(compact('affiliates', 'regions'));
		$this->set('_serialize', true);
		$this->render('index');
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->query('facility');
		try {
			$facility = $this->Facilities->get($id, [
				'contain' => [
					'Regions',
					'Fields' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Fields.is_open' => 'DESC', 'Fields.num']);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($facility->region->affiliate_id);

		$this->set(compact('facility'));
		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$facility = $this->Facilities->newEntity();
		if ($this->request->is('post')) {
			$facility = $this->Facilities->patchEntity($facility, $this->request->data);
			if ($this->Facilities->save($facility, ['fields' => $facility->fields])) {
				$this->Flash->success(__('The facility has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The facility could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->Facilities->Regions->affiliate($this->request->data['region_id']));
			}
		} else {
			$this->Facilities->patchEntity($facility, [
				'is_open' => true,
				'field' => [
					0 => [
						'is_open' => true,
					],
				],
			]);
		}

		$affiliates = $this->_applicableAffiliates(true);
		$regions = $this->Facilities->Regions->find('all', [
			'conditions' => ['Regions.affiliate_id IN' => array_keys($affiliates)],
			'contain' => ['Affiliates'],
			'order' => ['Affiliates.name', 'Regions.name'],
		]);
		if ($regions->isEmpty()) {
			$this->Flash->info(__('You must first create at least one region for facilities to be located in.'));
			return $this->redirect('/');
		} else if (count($affiliates) > 1) {
			$regions = collection($regions)->combine('id', 'name', 'affiliate.name')->toArray();
		} else {
			$regions = collection($regions)->combine('id', 'name')->toArray();
		}
		$this->set(compact('facility', 'regions', 'affiliates'));
		$this->_loadAddressOptions();
		$this->set('region', $this->request->query('region'));

		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->query('facility');
		try {
			$facility = $this->Facilities->get($id, [
				'contain' => [
					'Regions',
					'Fields' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Fields.num']);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($facility->region->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (!$this->request->data['is_open']) {
				foreach (array_keys($this->request->data['fields']) as $key) {
					$this->request->data['fields'][$key]['is_open'] = false;
				}
			}

			$facility = $this->Facilities->patchEntity($facility, $this->request->data);
			if ($this->Facilities->save($facility, ['fields' => $facility->fields])) {
				$this->Flash->success(__('The facility has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The facility could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$regions = $this->Facilities->Regions->find('list', [
				'conditions' => ['Regions.affiliate_id IN' => array_keys($affiliates)],
		])->toArray();
		$this->set(compact('facility', 'regions', 'affiliates'));
		$this->_loadAddressOptions();
		$this->set('region', $this->request->query('region'));
		$this->set('_serialize', true);
	}

	/**
	 * Add field function
	 *
	 * @return void Renders view, just an empty field block with a random index.
	 */
	public function add_field() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');
		$facility = $this->Facilities->newEntity();
		$this->set(compact('facility'));
	}

	/**
	 * Open facility method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 */
	public function open() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('facility');
		try {
			$facility = $this->Facilities->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		}

		$facility->is_open = true;
		if (!$this->Facilities->save($facility)) {
			$this->Flash->warning(__('Failed to open facility \'\'{0}\'\'.', addslashes($facility->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('facility'));
	}

	/**
	 * Close facility method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 */
	public function close() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('facility');
		try {
			$facility = $this->Facilities->get($id, [
				'contain' => ['Fields'],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		}

		$facility->is_open = false;
		foreach ($facility->fields as $field) {
			$field->is_open = false;
		}
		$facility->dirty('fields', true);

		if (!$this->Facilities->save($facility)) {
			$this->Flash->warning(__('Failed to close facility \'\'{0}\'\'.', addslashes($facility->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('facility'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('facility');
		$dependencies = $this->Facilities->dependencies($id, [], ['Fields']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this facility, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$facility = $this->Facilities->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid facility.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Facilities->delete($facility)) {
			$this->Flash->success(__('The facility has been deleted.'));
		} else if ($facility->errors('delete')) {
			$this->Flash->warning(current($facility->errors('delete')));
		} else {
			$this->Flash->warning(__('The facility could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
