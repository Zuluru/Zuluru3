<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

class OnTeamRule {
	/**
	 * Performs the roster check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		try {
			TableRegistry::getTableLocator()->get('TeamsPeople')->find()
				->where([
					'team_id' => $entity->team_id,
					'person_id' => $entity->person_id,
					'status' => ROSTER_APPROVED,
				])
				->firstOrFail();
		} catch (RecordNotFoundException $ex) {
			return false;
		}
		return true;
	}

}
