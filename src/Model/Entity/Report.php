<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Report Entity.
 *
 * @property int $id
 * @property string $report
 * @property int $person_id
 * @property string $params
 * @property int $failures
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\PhpbbReport[] $phpbb_reports
 */
class Report extends Entity {

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

}
