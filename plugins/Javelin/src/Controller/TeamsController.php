<?php
namespace Javelin\Controller;

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Query;

/**
 * Teams Controller
 */
class TeamsController extends AppController {

	public function join() {
		$id = $this->getRequest()->getQuery('team');
		$this->loadModel('Teams');

		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => [
					'People' => [
						'Settings' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Settings.name' => 'javelin']);
							}
						],
					],
				]]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division], 'Javelin'), 'join');

		$team->use_javelin = true;
		$event = new CakeEvent('Model.Team.rosterUpdate', $this, [$team]);
		$this->getEventManager()->dispatch($event);
		if (!$event->isStopped()) {
			$this->Teams->save($team);
			$this->Flash->success(__('Your team has been added to {0}.', 'Javelin'));
		} else {
			$this->Flash->error(__('Failed to add your team to {0}. You can try again; if the problem persists, contact support.', 'Javelin'));
		}

		return $this->redirect(['plugin' => null, 'controller' => 'Teams', 'action' => 'view', '?' => ['team' => $id]]);
	}

	public function leave() {
		$id = $this->getRequest()->getQuery('team');
		$this->loadModel('Teams');

		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division], 'Javelin'), 'leave');

		$event = new CakeEvent('Model.Team.rosterDelete', $this, [$team]);
		$this->getEventManager()->dispatch($event);
		if (!$event->isStopped()) {
			$team->use_javelin = false;
			$this->Teams->save($team);
			$this->Flash->success(__('Your team has been removed from {0}.', 'Javelin'));
		} else {
			$this->Flash->error(__('Failed to remove your team from {0}. You can try again; if the problem persists, contact support.', 'Javelin'));
		}

		return $this->redirect(['plugin' => null, 'controller' => 'Teams', 'action' => 'view', '?' => ['team' => $id]]);
	}

}
