<?php
namespace App\Shell\Task;

use App\Core\ModuleRegistry;
use Cake\Console\Shell;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * RecalculateRatings Task
 */
class RecalculateRatingsTask extends Shell {

	public function main() {
		$event = new CakeEvent('Controller.initialize', $this);
		EventManager::instance()->dispatch($event);

		// Find any leagues that are currently open, and possibly recalculate ratings
		$leagues = TableRegistry::get('Leagues')->find()
			->contain([
				'Divisions' => ['Teams'],
			])
			->where(['Leagues.is_open' => true])
			->order('Leagues.open');

		$moduleRegistry = ModuleRegistry::getInstance();
		foreach ($leagues as $league) {
			foreach ($league->divisions as $division) {
				$rating_obj = $moduleRegistry->load("Ratings:{$division->rating_calculator}");
				$rating_obj->recalculateRatings($division);
			}
		}
	}

}
