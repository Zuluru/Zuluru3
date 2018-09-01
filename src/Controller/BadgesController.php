<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Exception\MissingModuleException;
use App\Model\Entity\BadgesPerson;

/**
 * Badges Controller
 *
 * @property \App\Model\Table\BadgesTable $Badges
 */
class BadgesController extends AppController {

	public $paginate = [
		'order' => [
			'Badges.name',
		]
	];

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.badges')) {
				throw new MethodNotAllowedException('Badges are not enabled on this system.');
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations
				if (in_array($this->request->params['action'], [
					'add',
					'deactivated',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'view',
					'tooltip',
					'edit',
					'activate',
					'deactivate',
				])) {
					// If a badge id is specified, check if we're a manager of that badge's affiliate
					$badge = $this->request->query('badge');
					if ($badge) {
						if (in_array($this->Badges->affiliate($badge), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'index',
				'view',
				'tooltip',
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
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function index() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);

		$query = $this->Badges->find()
			->select(['count' => 'COUNT(People.id)'])
			->autoFields(true)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->leftJoin(['BadgesPeople' => 'badges_people'], ['BadgesPeople.badge_id = Badges.id'])
			->leftJoin(['People' => 'people'], ['BadgesPeople.person_id = People.id'])
			->where([
				'Badges.active' => true,
			])
			->group(['Badges.id']);

		if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager')) {
			// TODO: if manager, check we're not looking at another affiliate
			$query->andWhere(['Badges.visibility !=' => BADGE_VISIBILITY_ADMIN]);
		}

		$badges = $this->paginate($query);

		$this->set('active', true);
		$this->set(compact('affiliates', 'badges'));
	}

	public function deactivated() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);

		$query = $this->Badges->find()
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Badges.active' => false,
			])
			->group(['Badges.id']);

		if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager')) {
			// TODO: if manager, check we're not looking at another affiliate
			$query->andWhere(['Badges.visibility !=' => BADGE_VISIBILITY_ADMIN]);
		}

		$badges = $this->paginate($query);

		$this->set('active', false);
		$this->set(compact('affiliates', 'badges'));
		$this->render('index');
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function view() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		if ((!$badge->active || $badge->visibility == BADGE_VISIBILITY_ADMIN) && (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		// TODO: Multiple default sort fields break pagination links.
		// https://github.com/cakephp/cakephp/issues/7324 has related info.
		//$this->paginate['order'] = ['People.first_name', 'People.last_name'];
		$this->paginate['order'] = ['People.last_name'];
		$query = $this->Badges->People->find()
			->distinct(['People.id'])
			->contain([
				'Badges' => [
					'queryBuilder' => function (Query $q) use ($id) {
						return $q->where(['Badges.id' => $id]);
					},
				],
			])
			->matching('Badges', function (Query $q) use ($id) {
				return $q;
			})
			->where([
				'BadgesPeople.approved' => true,
				'BadgesPeople.badge_id' => $id,
			]);

		$badge->people = $this->paginate($query);

		$this->set(compact('badge'));
		$this->set('_serialize', true);
	}

	public function initialize_awards() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		if (in_array($badge->category, ['nominated', 'assigned', 'runtime', 'aggregate'])) {
			$this->Flash->info(__('This badge is {0}, not calculated, so it cannot be initialized.', __($badge->category)));
			return $this->redirect(['action' => 'index']);
		}
		if (empty($badge->handler)) {
			$this->Flash->info(__('This badge has no handler.'));
			return $this->redirect(['action' => 'index']);
		}

		$badge->refresh_from = 1;
		if ($this->Badges->save($badge)) {
			$this->Flash->info(__('This badge has been scheduled for re-initialization.'));
			return $this->redirect(['action' => 'view', 'badge' => $badge->id]);
		} else {
			$this->Flash->warning(__('Failed to schedule the badge for re-initialization.'));
			return $this->redirect(['action' => 'view', 'badge' => $badge->id]);
		}
	}

	public function tooltip() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id, [
				'contain' => ['People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['BadgesPeople.approved' => true]);
					},
				]],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		if ((!$badge->active || $badge->visibility == BADGE_VISIBILITY_ADMIN) && (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($badge->affiliate_id);
		$this->set(compact('badge'));
		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function add() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$badge = $this->Badges->newEntity();
		if ($this->request->is('post')) {
			$badge = $this->Badges->patchEntity($badge, $this->request->data);
			if ($this->Badges->save($badge)) {
				$this->Flash->success(__('The badge has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Configuration->loadAffiliate($this->request->data['affiliate_id']);
				$this->Flash->warning(__('The badge could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('badge', 'affiliates'));
		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$badge = $this->Badges->patchEntity($badge, $this->request->data);
			if ($this->Badges->save($badge)) {
				$this->Flash->success(__('The badge has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The badge could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('badge', 'affiliates'));
		$this->set('_serialize', true);
	}

	/**
	 * Activate method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function activate() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$badge->active = true;
		if (!$this->Badges->save($badge)) {
			$this->Flash->warning(__('Failed to activate badge \'\'{0}\'\'.', addslashes($badge->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('badge'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function deactivate() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$badge->active = false;
		if (!$this->Badges->save($badge)) {
			$this->Flash->warning(__('Failed to deactivate badge \'\'{0}\'\'.', addslashes($badge->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('badge'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('badge');
		$dependencies = $this->Badges->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this badge, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Badges->delete($badge)) {
			$this->Flash->success(__('The badge has been deleted.'));
		} else if ($badge->errors('delete')) {
			$this->Flash->warning(current($badge->errors('delete')));
		} else {
			$this->Flash->warning(__('The badge could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
