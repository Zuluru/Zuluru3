<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Client\Message;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Fields Controller
 *
 * @property \App\Model\Table\FieldsTable $Fields
 */
class FieldsController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['index', 'view', 'tooltip'];
	}

	/**
	 * This is here to support the many links to this page that are out there.
	 *
	 * @return \Cake\Http\Response Redirects
	 */
	public function index() {
		return $this->redirect(['controller' => 'Facilities', 'action' => 'index'], Message::STATUS_MOVED_PERMANENTLY);
	}

	/**
	 * This is here to support the many links to this page that are out there.
	 *
	 * @return \Cake\Http\Response Redirects
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('field');
		try {
			$facility_id = $this->Fields->field('facility_id', ['Fields.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		return $this->redirect(['controller' => 'Facilities', 'action' => 'view', 'facility' => $facility_id], Message::STATUS_MOVED_PERMANENTLY);
	}

	/**
	 * Tooltip method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function tooltip() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('field');
		try {
			$field = $this->Fields->get($id, [
				'contain' => [
					'Facilities' => ['Regions'],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($field->facility->region->affiliate_id);
		$this->set(compact('field'));
	}

	/**
	 * Open field method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function open() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('field');
		try {
			$field = $this->Fields->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}
		$this->Authorization->authorize($field);

		$field->is_open = true;
		if (!$this->Fields->save($field)) {
			$this->Flash->warning(__('Failed to open {0} "{1}".', Configure::read('UI.field'), addslashes($field->long_name)));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$this->set(compact('field'));
	}

	/**
	 * Close field method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function close() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('field');
		try {
			$field = $this->Fields->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}
		$this->Authorization->authorize($field);

		$field->is_open = false;
		if (!$this->Fields->save($field)) {
			$this->Flash->warning(__('Failed to close {0} "{1}".', Configure::read('UI.field'), addslashes($field->long_name)));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$this->set(compact('field'));
	}

	/**
	 * Delete method
	 *
	 * @return \Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('field');
		try {
			$field = $this->Fields->get($id, [
				'contain' => ['Facilities' => ['Fields']],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}
		$this->Authorization->authorize($field);

		$dependencies = $this->Fields->dependencies($id, ['Notes']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this {0}, so it cannot be deleted.', Configure::read('UI.field')) . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		if ($this->Fields->delete($field)) {
			$this->Flash->success(__('The {0} has been deleted.', Configure::read('UI.field')));
		} else {
			$errors = $field->getErrors();
			if (array_key_exists('delete', $errors)) {
				$this->Flash->warning(current($errors['delete']));
			} else {
				$this->Flash->warning(__('The {0} could not be deleted. Please, try again.', Configure::read('UI.field')));
			}
		}

		return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
	}

	/**
	 * Bookings method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function bookings() {
		$id = $this->getRequest()->getQuery('field');
		if ($this->Authentication->getIdentity()->isManager()) {
			$conditions = ['OR' => [
				'is_open' => true,
				'open >=' => FrozenDate::now(),
			]];
		} else {
			$conditions = ['is_open' => true];
		}

		$query = TableRegistry::getTableLocator()->get('Divisions')->find();
		$min_date = $query->select(['min' => $query->func()->min('open')])->where($conditions)->first()->min;
		$slot_conditions = ['GameSlots.game_date >=' => $min_date];
		if (!$this->Authentication->getIdentity()->isManager()) {
			$max_date = $query->select(['max' => $query->func()->max('close')])->where($conditions)->first()->max;
			$slot_conditions['GameSlots.game_date <='] = $max_date;
		}

		try {
			$field = $this->Fields->get($id, [
				'contain' => [
					'Facilities' => ['Regions'],
					'GameSlots' => [
						'queryBuilder' => function (Query $q) use ($slot_conditions) {
							return $q->where($slot_conditions)
								->order(['GameSlots.game_date', 'GameSlots.game_start']);
						},
						'Games' => [
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'OR' => [
										'Games.home_dependency_type !=' => 'copy',
										'Games.home_dependency_type IS' => null,
									],
								]);
							},
							'Divisions' => ['Leagues', 'Days'],
						],
						'Divisions' => ['Leagues', 'Days'],
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$this->Authorization->authorize($field);
		$this->Configuration->loadAffiliate($field->facility->region->affiliate_id);

		$this->set(compact('field'));
	}

}
