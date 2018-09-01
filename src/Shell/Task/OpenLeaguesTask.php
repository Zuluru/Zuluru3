<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * OpenLeagues Task
 */
class OpenLeaguesTask extends Shell {

	public function main() {
		$event = new CakeEvent('Controller.initialize', $this);
		EventManager::instance()->dispatch($event);

		$divisions_table = TableRegistry::get('Divisions');

		$to_close = $divisions_table->find()
			->where([
				'Divisions.is_open' => true,
				'OR' => [
					'Divisions.open >' => FrozenDate::now()->addDays(21),
					'Divisions.close <' => FrozenDate::now()->subDays(7),
				],
			])
			->order(['Divisions.open']);

		$to_open = $divisions_table->find()
			->where([
				'Divisions.is_open' => false,
				'Divisions.open <' => FrozenDate::now()->addDays(21),
				'Divisions.close >' => FrozenDate::now()->subDays(7),
			])
			->order(['Divisions.open']);

		foreach ($to_close as $division) {
			// Just tag it as dirty and re-save. The beforeSave will update is_open as required.
			$division->dirty('is_open', true);
			$divisions_table->save($division);
		}
		foreach ($to_open as $division) {
			// Just tag it as dirty and re-save. The beforeSave will update is_open as required.
			$division->dirty('is_open', true);
			$divisions_table->save($division);
		}
	}

}
