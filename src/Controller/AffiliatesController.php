<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use App\Model\Entity\AffiliatesPerson;

/**
 * Affiliates Controller
 *
 * @property \App\Model\Table\AffiliatesTable $Affiliates
 */
class AffiliatesController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);

		$affiliates = $this->Affiliates->find()
			->contain([
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['AffiliatesPeople.position' => 'manager']);
					},
				],
			]);

		$this->set(compact('affiliates'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('affiliate');
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

		$this->Authorization->authorize($affiliate);

		$this->set(compact('affiliate'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$affiliate = $this->Affiliates->newEntity();
		$this->Authorization->authorize($affiliate);

		if ($this->request->is('post')) {
			$affiliate = $this->Affiliates->patchEntity($affiliate, $this->request->getData());
			if ($this->Affiliates->save($affiliate)) {
				$this->Flash->success(__('The affiliate has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The affiliate could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->set(compact('affiliate'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('affiliate');
		try {
			$affiliate = $this->Affiliates->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($affiliate);
		$this->Configuration->loadAffiliate($id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$affiliate = $this->Affiliates->patchEntity($affiliate, $this->request->getData());
			if ($this->Affiliates->save($affiliate)) {
				$this->Flash->success(__('The affiliate has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The affiliate could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->set(compact('affiliate'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('affiliate');
		try {
			$affiliate = $this->Affiliates->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($affiliate);

		$dependencies = $this->Affiliates->dependencies($id, ['People', 'Settings']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this affiliate, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
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
	 */
	public function add_manager() {
		$id = $this->request->getQuery('affiliate');
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

		$this->Authorization->authorize($affiliate);
		$this->set(compact('affiliate'));

		$person_id = $this->request->getQuery('person');
		if ($person_id != null) {
			try {
				$person = $this->Affiliates->People->get($person_id, [
					'contain' => [
						'AffiliatesPeople' => [
							'queryBuilder' => function (Query $q) use ($id) {
								return $q->where(['AffiliatesPeople.id' => $id]);
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

			if (!empty($person->affiliates_people) && $person->affiliates_people[0]->position === 'manager') {
				$this->Flash->info(__('{0} is already a manager of this affiliate.', $person->full_name));
				return $this->redirect(['action' => 'view', 'affiliate' => $id]);
			} else {
				if (!empty($person->affiliates_people)) {
					$person->affiliates_people[0]->position = 'manager';
				} else {
					$person->affiliates_people = [new AffiliatesPerson(['affiliate_id' => $id, 'position' => 'manager'])];
				}
				$person->setDirty('affiliates_people', true);
				$success = $this->Affiliates->People->save($person);

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
	 */
	public function remove_manager() {
		$this->request->allowMethod(['post']);

		$id = $this->request->getQuery('affiliate');
		$person_id = $this->request->getQuery('person');
		try {
			$affiliate = $this->Affiliates->get($id, [
				'contain' => [
					'AffiliatesPeople' => [
						'queryBuilder' => function (Query $q) use ($person_id) {
							return $q->where([
								'AffiliatesPeople.person_id' => $person_id,
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

		$this->Authorization->authorize($affiliate);

		if (empty($affiliate->affiliates_people)) {
			$this->Flash->warning(__('That person is not a manager of this affiliate!'));
			return $this->redirect(['action' => 'view', 'affiliate' => $id]);
		}

		$affiliate->affiliates_people[0]->position = 'player';
		$affiliate->setDirty('affiliates_people', true);
		if ($this->Affiliates->save($affiliate)) {
			$this->Flash->success(__('Successfully removed manager.'));
			$this->Flash->success(__('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.'));
		} else {
			$this->Flash->warning(__('Failed to remove manager!'));
		}

		return $this->redirect(['action' => 'view', 'affiliate' => $id]);
	}

	public function select() {
		$this->Authorization->authorize($this);

		if ($this->request->is('post')) {
			$this->request->getSession()->write('Zuluru.CurrentAffiliate', $this->request->getData('affiliate'));
			return $this->redirect('/');
		}
		$affiliates = $this->Affiliates->find('list', [
			'conditions' => ['active' => true],
		]);
		$this->set(compact('affiliates'));
	}

	public function view_all() {
		$this->Authorization->authorize($this);

		$this->request->getSession()->delete('Zuluru.CurrentAffiliate');
		return $this->redirect('/');
	}
}
