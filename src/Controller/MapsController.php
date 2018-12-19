<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class MapsController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['index', 'view'];
	}

	public function index() {
		if ($this->Authentication->getIdentity() && $this->Authentication->getIdentity()->isManager()) {
			$closed = $this->request->getQuery('closed');
		} else {
			$closed = false;
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$regions_table = TableRegistry::get('Regions');
		$regions = $regions_table->find()
			->contain([
				'Facilities' => [
					'queryBuilder' => function (Query $q) use ($closed) {
						if (!$closed) {
							$q->andWhere(['Facilities.is_open' => true]);
						}
						return $q->order(['Facilities.name']);
					},
					'Fields' => [
						'queryBuilder' => function (Query $q) use ($closed) {
							$q->where(['Fields.latitude IS NOT' => null]);
							if (!$closed) {
								$q->andWhere(['Fields.is_open' => true]);
							}
							return $q;
						},
					],
				],
				'Affiliates',
			])
			->where(['Regions.affiliate_id IN' => $affiliates])
			->order('Regions.id');

		// TODOLATER: Option to limit by sport, maybe just on the map?

		$this->set(compact('regions', 'closed', 'affiliates'));

		$this->viewBuilder()->layout('map');
	}

	public function view() {
		$id = $this->request->getQuery('field');

		try {
			$facilities_table = TableRegistry::get('Facilities');
			$field = $facilities_table->Fields->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}
		if (!$field->latitude) {
			$this->Flash->info(__('That {0} has not yet been laid out.', __(Configure::read('UI.field'))));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$facility = $facilities_table->get($field->facility_id, [
			'contain' => [
				'Fields' => [
					'queryBuilder' => function (Query $q) use ($field) {
						$q->where(['Fields.latitude IS NOT' => null]);

						// When we're viewing open fields, only show open fields. This is the normal behaviour.
						if ($field->is_open) {
							$q->andWhere(['Fields.is_open' => true]);
						}

						return $q;
					},
				],
				'Regions',
			]
		]);
		$this->Configuration->loadAffiliate($facility->region->affiliate_id);

		$home_addr = '';
		if ($this->Authentication->getIdentity()) {
			$home_addr = $this->UserCache->read('Person.addr_street') . ', ' .
				$this->UserCache->read('Person.addr_city') . ', ' .
				$this->UserCache->read('Person.addr_prov');
		}
		$this->set(compact('field', 'facility', 'home_addr'));

		$this->viewBuilder()->layout('map');
	}

	public function edit() {
		$id = $this->request->getQuery('field');

		try {
			$facilities_table = TableRegistry::get('Facilities');
			$field = $facilities_table->Fields->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
			return $this->redirect(['controller' => 'Facilities', 'action' => 'index']);
		}

		$facility = $facilities_table->get($field->facility_id, [
			'contain' => [
				'Fields' => [
					'queryBuilder' => function (Query $q) {
						return $q->where([
							'Fields.is_open' => true,
						]);
					},
				],
				'Regions',
			]
		]);

		$this->Authorization->authorize($facility);
		$this->Configuration->loadAffiliate($facility->region->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$facility = $facilities_table->patchEntity($facility, $this->request->data, ['associated' => ['Fields']]);
			if ($facilities_table->save($facility)) {
				$this->Flash->warning(__('The {0} layout has been saved.', __(Configure::read('UI.field'))));
				return $this->redirect(['controller' => 'Maps', 'action' => 'view', 'field' => $id]);
			} else {
				$this->Flash->warning(__('The {0} layout could not be saved. Please correct the errors below and try again.', __(Configure::read('UI.field'))));
			}
		}

		// We use these as last-ditch emergency values, if the field has neither
		// a valid lat/long or an address that Google can find.
		$leaguelat = Configure::read('organization.latitude');
		$leaguelng = Configure::read('organization.longitude');
		if (empty($leaguelat) || empty($leaguelng)) {
			$this->Flash->info(__('Before using the layout editor, you must set the default latitude and longitude for your organization.'));
			return $this->redirect(['controller' => 'Settings', 'action' => 'organization']);
		}

		$this->set(compact('field', 'facility', 'leaguelat', 'leaguelng'));

		$this->viewBuilder()->layout('map');
	}

}
