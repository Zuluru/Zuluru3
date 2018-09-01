<?php
namespace App\Controller\Component;

use App\Core\UserCache;
use App\Event\FlashTrait;
use Cake\Controller\Component;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\TableRegistry;

class LockComponent extends Component {

	use FlashTrait;

	private $lock_id = null;

	public function shutdown(CakeEvent $cakeEvent) {
		$this->unlock();
	}

	public function lock($name, $affiliate = null, $text = null) {
		$this->Locks = TableRegistry::get('Locks');
		$conditions = ['name' => $name];
		if ($affiliate !== null) {
			$conditions['affiliate_id'] = $affiliate;
		}
		$locks = $this->Locks->find()->where($conditions);
		if ($locks->count() > 0) {
			$lock = $locks->first();
			if ($lock->created->addMinutes(15)->isPast()) {
				$this->Locks->delete($lock);
			} else {
				if ($text === null) {
					$text = $name;
				}
				$this->Flash('info', __('There is currently a {0} in progress. If unsuccessful, it will expire in 15 minutes.', __($text)));
				return false;
			}
		}

		$lock = $this->Locks->newEntity(['name' => $name, 'affiliate' => $affiliate, 'user_id' => UserCache::getInstance()->currentId()]);
		if ($this->Locks->save($lock)) {
			$this->lock_id = $lock->id;
			return true;
		}

		return false;
	}

	public function unlock() {
		if ($this->lock_id) {
			try {
				$lock = $this->Locks->get($this->lock_id);
				$this->Locks->delete($lock);
			} catch (RecordNotFoundException $ex) {
			} catch (InvalidPrimaryKeyException $ex) {
			}
			$this->lock_id = null;
		}
	}
}
