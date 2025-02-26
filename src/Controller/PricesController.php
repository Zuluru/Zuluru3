<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Prices Controller
 *
 * @property \App\Model\Table\PricesTable $Prices
 */
class PricesController extends AppController {

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('price');
		try {
			$price = $this->Prices->get($id, [
				'contain' => ['Events' => ['Prices']],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid price.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($price);

		$dependencies = $this->Prices->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this price point, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Events', 'action' => 'view', '?' => ['event' => $this->Prices->event($id)]]);
		}

		if ($this->Prices->delete($price)) {
			$this->Flash->success(__('The price point has been deleted.'));
		} else if ($price->getError('delete')) {
			$this->Flash->warning(current($price->getError('delete')));
		} else {
			$this->Flash->warning(__('The price point could not be deleted. Please, try again.'));
		}

		return $this->redirect(['controller' => 'Events', 'action' => 'view', '?' => ['event' => $price->event_id]]);
	}

}
