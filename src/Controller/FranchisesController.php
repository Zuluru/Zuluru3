<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;

/**
 * Franchises Controller
 *
 * @property \App\Model\Table\FranchisesTable $Franchises
 */
class FranchisesController extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	protected function _publicActions() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		if (Configure::read('Perm.is_manager')) {
			// If a franchise id is specified, check if we're a manager of that franchise's affiliate
			$franchise = $this->request->query('franchise');
			if ($franchise) {
				if (!in_array($this->Franchises->affiliate($franchise), $this->UserCache->read('ManagedAffiliateIDs'))) {
					Configure::write('Perm.is_manager', false);
				}
			}
		}

		return ['index', 'letter', 'view'];
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.franchises')) {
				throw new MethodNotAllowedException('Franchises are not enabled on this system.');
			}

			// People can perform these operations on franchises they run
			if (in_array($this->request->params['action'], [
				'edit',
				'delete',
				'add_owner',
				'remove_team',
				'remove_owner',
			])) {
				// If a franchise id is specified, check if we're the owner of that franchise
				$franchise = $this->request->query('franchise');
				if ($franchise && in_array($franchise, $this->UserCache->read('FranchiseIDs'))) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if ($franchise && Configure::read('Perm.is_manager')) {
					if (in_array($this->Franchises->affiliate($franchise), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'add',
			])) {
				return true;
			}

			// People can perform these operations on teams they run
			if (in_array($this->request->params['action'], [
				'add_team',
			])) {
				// If a franchise id is specified, check if we're an owner of that franchise
				$franchise = $this->request->query('franchise');
				if ($franchise && in_array($franchise, $this->UserCache->read('FranchiseIDs'))) {
					// If no team id is specified, or if we're the owner of the specified team, we can proceed
					$team = $this->request->query('team');
					if (!$team || in_array($team, $this->UserCache->read('AllOwnedTeamIDs'))) {
						return true;
					}
				}
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
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function index() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('affiliates', 'affiliate'));

		$this->paginate = [
			'conditions' => ['Franchises.affiliate_id IN' => $affiliates],
			'contain' => ['People', 'Affiliates'],
			'order' => ['Franchises.name'],
			'limit' => Configure::read('feature.items_per_page'),
		];

		$query = $this->Franchises->find()
			->order(['Affiliates.name']);
		$this->set('franchises', $this->paginate($query));

		$letters = $this->Franchises->find()
			->hydrate(false)
			->select(['letter' => 'DISTINCT SUBSTR(Franchises.name, 1, 1)'])
			->where([
				'Franchises.affiliate_id IN' => $affiliates,
			])
			->order(['letter'])
			->toArray();
		$this->set(compact('letters'));

		$this->set('_serialize', true);
	}

	public function letter() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$letter = strtoupper($this->request->query('letter'));
		if (!$letter) {
			$this->Flash->info(__('Invalid letter.'));
			return $this->redirect(['action' => 'index']);
		}

		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('letter', 'affiliates', 'affiliate'));

		$franchises = $this->Franchises->find()
			->contain(['People', 'Affiliates'])
			->where([
				'Franchises.affiliate_id IN' => $affiliates,
				'Franchises.name LIKE' => "$letter%",
			])
			->order(['Affiliates.name', 'Franchises.name'])
			->toArray();

		$letters = $this->Franchises->find()
			->hydrate(false)
			->select(['letter' => 'DISTINCT SUBSTR(Franchises.name, 1, 1)'])
			->where([
				'Franchises.affiliate_id IN' => $affiliates,
			])
			->order(['letter'])
			->toArray();

		$this->set(compact('franchises', 'letters', 'letter'));
		$this->set('_serialize', true);
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function view() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$id = $this->request->query('franchise');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => [
					'Teams' => ['Divisions' => ['Leagues']],
					'People',
					'Affiliates',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($franchise->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('franchise', 'affiliates'));

		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function add() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$franchise = $this->Franchises->newEntity();
		if ($this->request->is('post')) {
			$this->request->data['people'] = ['_ids' => [$this->UserCache->currentId()]];
			$franchise = $this->Franchises->patchEntity($franchise, $this->request->data);
			if ($this->Franchises->save($franchise)) {
				$this->Flash->success(__('The franchise has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The franchise could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($franchise->affiliate_id);
			}
		}
		$this->set(compact('franchise'));
		$this->set('affiliates', $this->_applicableAffiliates(true));

		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$id = $this->request->query('franchise');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => ['People']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($franchise->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$franchise = $this->Franchises->patchEntity($franchise, $this->request->data);
			if ($this->Franchises->save($franchise)) {
				$this->Flash->success(__('The franchise has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The franchise could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('franchise', 'affiliates', 'people', 'teams'));
		$this->set('_serialize', true);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('franchise');
		$dependencies = $this->Franchises->dependencies($id, ['People']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this franchise, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => ['People'],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Franchises->delete($franchise)) {
			$this->Flash->success(__('The franchise has been deleted.'));
		} else if ($franchise->errors('delete')) {
			$this->Flash->warning(current($franchise->errors('delete')));
		} else {
			$this->Flash->warning(__('The franchise could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Add team to franchise method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function add_team() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$id = $this->request->query('franchise');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => [
					'Teams',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($franchise->affiliate_id);

		$teams = $this->UserCache->read('AllOwnedTeams');
		if ($this->request->data) {
			if (collection($franchise->teams)->firstMatch(['id' => $this->request->data['team_id']])) {
				$this->Flash->info(__('That team is already part of this franchise.'));
			} else {
				$team = collection($teams)->firstMatch(['id' => $this->request->data['team_id']]);
				if (!$team) {
					$this->Flash->info(__('You are not a captain, assistant captain or coach of the selected team.'));
				}
				else {
					if ($this->Franchises->Teams->link($franchise, [$team])) {
						$this->Flash->success(__('The selected team has been added to this franchise.'));
						return $this->redirect(['action' => 'view', 'franchise' => $id]);
					} else {
						$this->Flash->warning(__('Failed to add the selected team to this franchise.'));
					}
				}
			}
		}

		$this->set(compact('teams', 'franchise'));

	}

	/**
	 * Remove team from franchise method
	 *
	 * @return void|\Cake\Network\Response Redirects to view.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function remove_team() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$this->request->allowMethod(['post']);

		$id = $this->request->query('franchise');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => [
					'Teams',
					'People',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($franchise->affiliate_id);

		$team_id = $this->request->query('team');
		if (!$team_id) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		if (!collection($franchise->teams)->match(['id' => $team_id])) {
			$this->Flash->info(__('That team is not part of this franchise.'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		try {
			$team = $this->Franchises->Teams->get($team_id, [
				'contain' => [
					'Franchises',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		if (count($team->franchises) == 1) {
			$this->Flash->info(__('All teams must be members of at least one franchise. Before you can remove this team from this franchise, you must first add it to another one.'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		$this->Franchises->Teams->unlink($franchise, [$team], false);

		// If this was the only team in the franchise, delete the franchise too
		if (count($franchise->teams) == 1) {
			if ($this->Franchises->delete($franchise)) {
				$this->Flash->warning(__('The selected team has been removed from this franchise.') . ' ' .
					__('As there were no other teams in the franchise, it has been deleted as well.'));
				return $this->redirect('/');
			} else {
				$this->Flash->warning(__('The selected team has been removed from this franchise.') . ' ' .
					__('There are no other teams in the franchise, but deletion of the franchise failed.'));
			}
		} else {
			$this->Flash->success(__('The selected team has been removed from this franchise.'));
		}

		return $this->redirect(['action' => 'view', 'franchise' => $id]);
	}

	/**
	 * Add owner to franchise method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function add_owner() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$id = $this->request->query('franchise');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => [
					'People',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($franchise->affiliate_id);

		$this->set(compact('franchise'));

		$person_id = $this->request->query('person');
		if ($person_id != null) {
			try {
				$person = $this->Franchises->People->get($person_id, [
					'contain' => [
						'Franchises' => [
							'queryBuilder' => function (Query $q) use ($id) {
								return $q->where(['Franchises.id' => $id]);
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

			if (!empty($person->franchises)) {
				$this->Flash->info(__('{0} is already an owner of this franchise.', $person->full_name));
				return $this->redirect(['action' => 'add_owner', 'franchise' => $id]);
			} else {
				if ($this->Franchises->People->link($franchise, [$person])) {
					$this->Flash->success(__('Added {0} as owner.', $person->full_name));
					return $this->redirect(['action' => 'view', 'franchise' => $id]);
				} else {
					$this->Flash->warning(__('Failed to add {0} as owner.', $person->full_name));
					return $this->redirect(['action' => 'add_owner', 'franchise' => $id]);
				}
			}
		}

		$this->_handlePersonSearch(['franchise', 'person'], ['group_id IN' => [GROUP_PLAYER,GROUP_COACH,GROUP_PARENT]]);
	}

	/**
	 * Remove owner from franchise method
	 *
	 * @return void|\Cake\Network\Response Redirects to view.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if franchises are not enabled
	 */
	public function remove_owner() {
		if (!Configure::read('feature.franchises')) {
			throw new MethodNotAllowedException('Franchises are not enabled on this system.');
		}

		$this->request->allowMethod(['post']);

		$id = $this->request->query('franchise');
		$person_id = $this->request->query('person');
		try {
			$franchise = $this->Franchises->get($id, [
				'contain' => [
					'People',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid franchise.'));
			return $this->redirect(['action' => 'index']);
		}

		if (count($franchise->people) == 1) {
			$this->Flash->warning(__('You cannot remove the only owner of a franchise!'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		// Eliminate all but the requested person
		$franchise->people = collection($franchise->people)->match(['id' => $person_id])->toList();
		if (empty($franchise->people)) {
			$this->Flash->warning(__('That person is not an owner of this franchise!'));
			return $this->redirect(['action' => 'view', 'franchise' => $id]);
		}

		$this->Franchises->People->unlink($franchise, $franchise->people, false);
		$this->Flash->success(__('Successfully removed owner.'));

		return $this->redirect(['action' => 'view', 'franchise' => $id]);
	}

}
