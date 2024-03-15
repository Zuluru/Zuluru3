<?php
namespace Javelin\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Register Controller
 */
class RegisterController extends AppController {

	public function index() {
		$this->Authorization->authorize($this);

		$person_id = $this->getRequest()->getQuery('person');
		if ($person_id != null) {
			try {
				$person = TableRegistry::getTableLocator()->get('People')->get($person_id);

				$event = new Event('Javelin.register', $this, [$person]);
				$this->getEventManager()->dispatch($event);

				if (!$event->isStopped()) {
					$this->Flash->success(__('Your site has been registered with {0}.', 'Javelin'));
					return $this->redirect(['plugin' => null, 'controller' => 'Settings', 'action' => 'team']);
				} else {
					$this->Flash->warning(__('Failed to register with {0}. Please try again. If you have continued problems, please contact {0} support}.', 'Javelin'));
				}
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
			}
		}

		$this->_handlePersonSearch([], ['group_id IN' => [GROUP_OFFICIAL,GROUP_MANAGER,GROUP_ADMIN]]);
	}

}
