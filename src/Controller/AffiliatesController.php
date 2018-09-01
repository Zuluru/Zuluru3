<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use App\Model\Entity\AffiliatesPerson;

/**
 * Affiliates Controller
 *
 * @property \App\Model\Table\AffiliatesTable $Affiliates
 */
class AffiliatesController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.affiliates')) {
				throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
			}

			if (Configure::read('Perm.is_manager')) {
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->request->query('affiliate');
				if ($affiliate && !in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
					Configure::write('Perm.is_manager', false);
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'select',
				'view_all',
			])) {
				return true;
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function index() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$affiliates = $this->Affiliates->find()
			->contain([
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['AffiliatesPeople.position' => 'manager']);
					},
				],
			]);

		$this->set(compact('affiliates'));
		$this->set('_serialize', true);
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function view() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$id = $this->request->query('affiliate');
		try {
			$affiliate = $this->Affiliates->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['AffiliatesPeople.position' => 'manager']);
						},
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('affiliate'));
		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function add() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$affiliate = $this->Affiliates->newEntity();
		if ($this->request->is('post')) {
			$affiliate = $this->Affiliates->patchEntity($affiliate, $this->request->data);
			if ($this->Affiliates->save($affiliate)) {
				$this->Flash->success(__('The affiliate has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The affiliate could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->set(compact('affiliate'));
		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$id = $this->request->query('affiliate');
		try {
			$affiliate = $this->Affiliates->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$affiliate = $this->Affiliates->patchEntity($affiliate, $this->request->data);
			if ($this->Affiliates->save($affiliate)) {
				$this->Flash->success(__('The affiliate has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The affiliate could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->set(compact('affiliate'));
		$this->set('_serialize', true);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('affiliate');
		$dependencies = $this->Affiliates->dependencies($id, ['People', 'Settings']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this affiliate, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$affiliate = $this->Affiliates->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Affiliates->delete($affiliate)) {
			$this->Flash->success(__('The affiliate has been deleted.'));
		} else if ($affiliate->errors('delete')) {
			$this->Flash->warning(current($affiliate->errors('delete')));
		} else {
			$this->Flash->warning(__('The affiliate could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Add manager method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function add_manager() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$id = $this->request->query('affiliate');
		try {
			$affiliate = $this->Affiliates->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['AffiliatesPeople.position' => 'manager']);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('affiliate'));

		$person_id = $this->request->query('person');
		if ($person_id != null) {
			try {
				$person = $this->Affiliates->People->get($person_id, [
					'contain' => [
						'Affiliates' => [
							'queryBuilder' => function (Query $q) use ($id) {
								return $q->where(['Affiliates.id' => $id]);
							},
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['action' => 'index']);
			}

			if (!empty($person->affiliates) && $person->affiliates[0]->_joinData->position == 'manager') {
				$this->Flash->info(__('{0} is already a manager of this affiliate.', $person->full_name));
				return $this->redirect(['action' => 'view', 'affiliate' => $id]);
			} else {
				if (!empty($person->affiliates)) {
					$person->affiliates[0]->_joinData->position = 'manager';
					$person->dirty('affiliates', true);
					$success = $this->Affiliates->People->save($person);
				} else {
					$person->_joinData = new AffiliatesPerson(['position' => 'manager']);
					$success = $this->Affiliates->People->link($affiliate, [$person]);
				}
				if ($success) {
					$this->Flash->success(__('Added {0} as manager.', $person->full_name));
					return $this->redirect(['action' => 'view', 'affiliate' => $id]);
				} else {
					$this->Flash->warning(__('Failed to add {0} as manager.', $person->full_name));
				}
			}
		}

		$this->_handlePersonSearch(['affiliate', 'person'], ['group_id IN' => [GROUP_MANAGER,GROUP_ADMIN]]);
	}

	/**
	 * Remove manager method
	 *
	 * @return void|\Cake\Network\Response Redirects to view.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if affiliates are not enabled
	 */
	public function remove_manager() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$this->request->allowMethod(['post']);

		$id = $this->request->query('affiliate');
		$person_id = $this->request->query('person');
		try {
			$affiliate = $this->Affiliates->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) use ($person_id) {
							return $q->where([
								'People.id' => $person_id,
								'AffiliatesPeople.position' => 'manager',
							]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		if (empty($affiliate->people)) {
			$this->Flash->warning(__('That person is not a manager of this affiliate!'));
			return $this->redirect(['action' => 'view', 'affiliate' => $id]);
		}

		$affiliate->people[0]->_joinData->position = 'player';
		$affiliate->dirty('people', true);
		if ($this->Affiliates->save($affiliate)) {
			$this->Flash->success(__('Successfully removed manager.'));
			$this->Flash->success(__('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.'));
		} else {
			$this->Flash->warning(__('Failed to remove manager!'));
		}

		return $this->redirect(['action' => 'view', 'affiliate' => $id]);
	}

	public function select() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		if ($this->request->is('post')) {
			$this->request->session()->write('Zuluru.CurrentAffiliate', $this->request->data['affiliate']);
			return $this->redirect('/');
		}
		$affiliates = $this->Affiliates->find('list', [
			'conditions' => ['active' => true],
		]);
		$this->set(compact('affiliates'));
	}

	public function view_all() {
		if (!Configure::read('feature.affiliates')) {
			throw new MethodNotAllowedException('Affiliates are not enabled on this system.');
		}

		$this->request->session()->delete('Zuluru.CurrentAffiliate');
		return $this->redirect('/');
	}
}
