<?php
namespace App\Shell\Task;

use App\Core\ModuleRegistry;
use App\Middleware\ConfigurationLoader;
use Cake\Console\Shell;
use Cake\ORM\TableRegistry;

/**
 * RecalculateRatings Task
 */
class RecalculateRatingsTask extends Shell {

	public function main() {
		ConfigurationLoader::loadConfiguration();
		// Find any leagues that are currently open, and possibly recalculate ratings
		$leagues = TableRegistry::getTableLocator()->get('Leagues')->find()
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
