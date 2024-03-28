<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Credits Controller
 *
 * @property \App\Model\Table\CreditsTable $Credits
 */
class CreditsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$all = $this->getRequest()->getQuery('all') ? true : false;

		$conditions = [
			'Credits.affiliate_id IN' => $affiliates,
		];
		if (!$all) {
			$conditions[] = 'Credits.amount != Credits.amount_used';
		}

		$credits = $this->Credits->find()
			->where($conditions)
			->contain([
				'Affiliates',
				'People' => [Configure::read('Security.authModel')],
			])
			->order(['Credits.affiliate_id', 'Credits.created']);
		if ($credits->isEmpty()) {
			$this->Flash->info($all ? __('There are no credits.') : __('There are no unused credits.'));
			return $this->redirect('/');
		}

		if ($this->getRequest()->is('csv')) {
			$date = FrozenDate::now()->format('Y-m-d');
			$this->setResponse($this->getResponse()->withDownload("Credits - {$date}.csv"));
		} else {
			$credits = $this->paginate($credits);
		}

		$credits = $credits->toArray();
		$this->set(compact('credits', 'affiliates', 'all'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('credit');
		try {
			$credit = $this->Credits->get($id, [
				'contain' => ['People', 'Payments']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid credit.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($credit);

		$this->set(compact('credit'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$credit = $this->Credits->newEmptyEntity();

		$id = $this->getRequest()->getQuery('person');
		try {
			$credit->person = $this->Credits->People->get($id, [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($credit);
		if ($this->getRequest()->is('post')) {
			$credit = $this->Credits->patchEntity($credit, array_merge($this->getRequest()->getData(), ['person_id' => $id]));
			if ($this->Credits->save($credit)) {
				$this->Flash->success(__('The credit has been saved.'));

				$this->_sendMail([
					'to' => $credit->person,
					'subject' => function() { return __('{0} Credit added', Configure::read('organization.name')); },
					'template' => 'credit_added',
					'sendAs' => 'both',
					'viewVars' => [
						'credit' => $credit,
						'person' => $credit->person,
					],
				]);

				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The credit could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('credit'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('credit');
		try {
			$credit = $this->Credits->get($id, [
				'contain' => [
					'People' => [Configure::read('Security.authModel')],
					'Payments' => ['Payments'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid credit.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($credit);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$credit = $this->Credits->patchEntity($credit, $this->getRequest()->getData());
			if ($this->Credits->save($credit)) {
				$this->Flash->success(__('The credit has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The credit could not be saved. Please correct the errors below and try again.'));

				if ($credit->payment && $credit->payment->getError('payment_amount')) {
					$credit->setErrors(['amount' => $credit->payment->getError('payment_amount')]);
				}
			}
		}

		$this->set(compact('credit'));
	}

	public function transfer() {
		$id = $this->getRequest()->getQuery('credit');
		try {
			$credit = $this->Credits->get($id, [
				'contain' => [
					'People',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid credit.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($credit);
		$this->Configuration->loadAffiliate($credit->affiliate_id);

		$person_id = $this->getRequest()->getQuery('person');
		if ($person_id) {
			try {
				$person = $this->Credits->People->get($person_id, [
					'contain' => [Configure::read('Security.authModel')]
				]);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}

			if (in_array($person->status, ['inactive', 'locked'])) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}

			if ($credit->person_id == $person_id) {
				$this->Flash->warning(__('This is the person who already owns the credit.'));
			} else {
				$this->UserCache->clear('Credits', $credit->person_id);
				$credit = $this->Credits->patchEntity($credit, [
					'person_id' => $person_id,
					'notes' => $credit->notes . "\n" . __('Transferred from {0}.', $credit->person->full_name),
				]);
				if ($this->Credits->save($credit)) {
					$this->Flash->success(__('The credit has been transferred.'));

					$this->_sendMail([
						'to' => $person,
						'subject' => function() { return __('{0} Credit transferred', Configure::read('organization.name')); },
						'template' => 'credit_transferred',
						'sendAs' => 'both',
						'viewVars' => [
							'credit' => $credit,
							'person' => $person,
						],
					]);

					return $this->redirect('/');
				} else {
					$this->Flash->warning(__('The credit could not be transferred. Please try again. If you have continued problems, please contact the office.'));
				}
			}
		}

		// Get all this person's relatives, and also their relatives, to make it easy to transfer among family
		$relative_ids = array_unique(array_merge(
			$this->UserCache->read('RelativeIDs', $credit->person_id),
			$this->UserCache->read('RelatedToIDs', $credit->person_id)
		));
		$relatives = $relative_ids;
		foreach ($relative_ids as $relative) {
			$relatives += array_merge(
				$this->UserCache->read('RelativeIDs', $relative),
				$this->UserCache->read('RelatedToIDs', $relative)
			);
		}
		if (!empty($relatives)) {
			$relatives = $this->Credits->People->find()
				->where([
					'People.id IN' => $relatives,
					'People.id !=' => $credit->person_id,
					'People.status !=' => 'inactive',
				])
				->toArray();
		}

		// Find all recent, current and upcoming teams, and their captains
		$teams = TableRegistry::getTableLocator()->get('Teams')->find()
			->contain(['Divisions'])
			->matching('People', function (Query $q) use ($credit) {
				return $q->where(['People.id' => $credit->person_id]);
			})
			->where([
				'Teams.division_id IS NOT' => null,
				'Divisions.close >' => FrozenDate::now()->subMonths(3),
			])
			->extract('id')
			->toArray();
		if (!empty($teams)) {
			$captains = $this->Credits->People->find()
				->distinct()
				->matching('Teams', function (Query $q) use ($teams) {
					return $q->where([
						'Teams.id IN' => $teams,
						'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
					]);
				})
				->toArray();
		} else {
			$captains = [];
		}

		$this->set(compact('credit', 'relatives', 'captains'));
		$this->_handlePersonSearch(['credit']);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('credit');
		$dependencies = $this->Credits->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this credit, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$credit = $this->Credits->get($id, [
				'contain' => ['Payments' => ['Payments']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid credit.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($credit);

		// If there is a related payment, we want to delete that, and the deletion will cascade to the credit
		if ($credit->payment) {
			$result = $this->Credits->Payments->delete($credit->payment);
		} else {
			$result = $this->Credits->delete($credit);
		}
		if ($result) {
			$this->Flash->success(__('The credit has been deleted.'));
		} else if ($credit->getError('delete')) {
			$this->Flash->warning(current($credit->getError('delete')));
		} else if ($credit->payment && $credit->payment->getError('delete')) {
			$this->Flash->warning(current($credit->payment->getError('delete')));
		} else {
			$this->Flash->warning(__('The credit could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
