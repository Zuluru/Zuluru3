<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * DeactivateAccounts Task
 */
class DeactivateAccountsTask extends Shell {

	public function main() {
		$event = new CakeEvent('Controller.initialize', $this);
		EventManager::instance()->dispatch($event);

		$people_table = TableRegistry::get('People');

		// Find all the divisions that have run in the past 2 years
		$recent_date = FrozenDate::now()->subYears(2);
		$divisions = $people_table->Divisions->find()
			->contain(['People'])
			->where(['Divisions.close >' => $recent_date]);

		$recent_people = array_unique(array_merge(
			// Include everyone that ran a division
			$divisions->extract('people.{*}.id')->toList(),
			// Or played in one
			TableRegistry::get('TeamsPeople')->find()
				->hydrate(false)
				->select('TeamsPeople.person_id')
				->distinct('TeamsPeople.person_id')
				->leftJoinWith('Teams')
				->where([
					'Teams.division_id IN' => $divisions->extract('id')->toList(),
					'TeamsPeople.status' => ROSTER_APPROVED,
				])
				->extract('person_id')
				->toArray(),
			// Or signed a waiver
			TableRegistry::get('WaiversPeople')->find()
				->hydrate(false)
				->select('WaiversPeople.person_id')
				->distinct('WaiversPeople.person_id')
				->where([
					'WaiversPeople.created >' => $recent_date,
				])
				->extract('person_id')
				->toArray(),
			// Or updated their profile
			$people_table->find()
				->hydrate(false)
				->select('People.id')
				->distinct('People.id')
				->where([
					'OR' => [
						'People.modified >' => $recent_date,
					],
				])
				->extract('id')
				->toArray()
		));

		if (Configure::read('feature.registration')) {
			// Or has registered for anything in the past 2 years
			$recent_people = array_unique(array_merge(
				$recent_people,
				$people_table->Registrations->find()
					->hydrate(false)
					->select('Registrations.person_id')
					->distinct('Registrations.person_id')
					->where([
						'Registrations.created >' => $recent_date,
						'Registrations.payment IN' => Configure::read('registration_reserved'),
					])
					->extract('person_id')
					->toArray()
			));
		}

		// Deactivate anyone whose account is active but is not included in the "recent activity" list
		$to_deactivate = $people_table->find()->where(['status' => 'active']);
		if (!empty($recent_people)) {
			$to_deactivate->andWhere(['id NOT IN' => array_unique($recent_people)]);
		}

		$people_table->removeBehavior('Timestamp');
		foreach ($to_deactivate as $person) {
			$person->status = 'inactive';
			$people_table->save($person, ['checkRules' => false]);
		}
	}

}
