<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;

/**
 * Badges Controller
 *
 * @property \App\Model\Table\BadgesTable $Badges
 */
class BadgesController extends AppController {

	public $paginate = [
		'order' => ['Badges.affiliate_id' => 'ASC', 'Badges.name' => 'ASC']
	];

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$query = $this->Badges->find()
			->select(['count' => 'COUNT(People.id)'])
			->enableAutoFields(true)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->leftJoin(['BadgesPeople' => 'badges_people'], ['BadgesPeople.badge_id = Badges.id'])
			->leftJoin(['People' => 'people'], ['BadgesPeople.person_id = People.id'])
			->where([
				'Badges.active' => true,
			])
			->group(['Badges.id']);

		if (!$this->Authentication->getIdentity()->isManager()) {
			// TODO: if manager, check we're not looking at another affiliate
			$query->andWhere(['Badges.visibility !=' => BADGE_VISIBILITY_ADMIN]);
		}

		$badges = $this->paginate($query);

		$this->set('active', true);
		$this->set(compact('affiliates', 'badges'));
	}

	public function deactivated() {
		$this->Authorization->authorize($this);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$query = $this->Badges->find()
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Badges.active' => false,
			])
			->group(['Badges.id']);


		$badges = $this->paginate($query);

		$this->set('active', false);
		$this->set(compact('affiliates', 'badges'));
		$this->render('index');
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($badge);
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		// TODO: Multiple default sort fields break pagination links.
		// https://github.com/cakephp/cakephp/issues/7324 has related info.
		//$this->paginate['order'] = ['People.first_name' => 'ASC', 'People.last_name' => 'ASC'];
		$this->paginate['order'] = ['People.last_name' => 'ASC'];
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
	}

	public function initialize_awards() {
		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($badge, 'edit');

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
			return $this->redirect(['action' => 'view', '?' => ['badge' => $badge->id]]);
		} else {
			$this->Flash->warning(__('Failed to schedule the badge for re-initialization.'));
			return $this->redirect(['action' => 'view', '?' => ['badge' => $badge->id]]);
		}
	}

	public function tooltip() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id, [
				'contain' => ['People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['BadgesPeople.approved' => true]);
					},
				]],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($badge, 'view');
		$this->Configuration->loadAffiliate($badge->affiliate_id);
		$this->set(compact('badge'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$badge = $this->Badges->newEmptyEntity();
		$this->Authorization->authorize($this);

		if ($this->getRequest()->is('post')) {
			$badge = $this->Badges->patchEntity($badge, $this->getRequest()->getData());
			if ($this->Badges->save($badge)) {
				$this->Flash->success(__('The badge has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Configuration->loadAffiliate($this->getRequest()->getData('affiliate_id'));
				$this->Flash->warning(__('The badge could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('badge', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($badge);
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$badge = $this->Badges->patchEntity($badge, $this->getRequest()->getData());
			if ($this->Badges->save($badge)) {
				$this->Flash->success(__('The badge has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The badge could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('badge', 'affiliates'));
	}

	/**
	 * Activate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Authorization->authorize($badge, 'edit');

		$badge->active = true;
		if (!$this->Badges->save($badge)) {
			$this->Flash->warning(__('Failed to activate badge "{0}".', addslashes($badge->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('badge'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('badge');
		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Authorization->authorize($badge, 'edit');

		$badge->active = false;
		if (!$this->Badges->save($badge)) {
			$this->Flash->warning(__('Failed to deactivate badge "{0}".', addslashes($badge->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('badge'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('badge');

		try {
			$badge = $this->Badges->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($badge);

		$dependencies = $this->Badges->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this badge, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Badges->delete($badge)) {
			$this->Flash->success(__('The badge has been deleted.'));
		} else if ($badge->getError('delete')) {
			$this->Flash->warning(current($badge->getError('delete')));
		} else {
			$this->Flash->warning(__('The badge could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
