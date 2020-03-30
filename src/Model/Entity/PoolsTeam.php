<?php
namespace App\Model\Entity;

use Cake\I18n\Number;
use Cake\ORM\Entity;

/**
 * PoolsTeam Entity.
 *
 * @property int $id
 * @property int $pool_id
 * @property string $alias
 * @property string $dependency_type
 * @property int $dependency_ordinal
 * @property int $dependency_pool_id
 * @property int $dependency_id
 * @property int $team_id
 *
 * @property \App\Model\Entity\Pool $pool
 * @property \App\Model\Entity\Pool $dependency_pool
 * @property \App\Model\Entity\Team $team
 */
class PoolsTeam extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	public function dependency() {
		if (!empty($this->dependency_pool_id)) {
			if (!$this->has('dependency_pool')) {
				trigger_error('Missing dependency information', E_USER_ERROR);
			}
			if ($this->dependency_pool->type == 'crossover') {
				if ($this->dependency_id == 1) {
					return __('winner of {0}', $this->dependency_pool->translateField('name'));
				} else {
					return __('loser of {0}', $this->dependency_pool->translateField('name'));
				}
			} else {
				return __('{0} in pool {1}', Number::ordinal($this->dependency_id), $this->dependency_pool->translateField('name'));
			}
		} else if (!empty($this->dependency_ordinal)) {
			return __('{0} among {1} place teams', Number::ordinal($this->dependency_id), Number::ordinal($this->dependency_ordinal));
		} else {
			return __('{0} seed', Number::ordinal($this->dependency_id));
		}
	}

}
