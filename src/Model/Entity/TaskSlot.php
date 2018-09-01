<?php
namespace App\Model\Entity;

use App\Model\Traits\DateTimeCombinator;
use Cake\ORM\Entity;

/**
 * TaskSlot Entity.
 *
 * @property int $id
 * @property int $task_id
 * @property \Cake\I18n\FrozenDate $task_date
 * @property \Cake\I18n\FrozenTime $task_start
 * @property \Cake\I18n\FrozenTime $task_end
 * @property int $person_id
 * @property bool $approved
 * @property int $approved_by_id
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Task $task
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Person $approved_by
 */
class TaskSlot extends Entity {

	use DateTimeCombinator;

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

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'start_time',
		'end_time',
	];

	private $_dateTimeCombinatorFields = [
		'date' => 'task_date',
		'start' => 'task_start',
		'end' => 'task_end',
	];

}
