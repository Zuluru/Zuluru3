<?php
namespace App\Model\Entity;

use App\Model\Traits\DateTimeCombinator;
use Cake\ORM\Entity;

/**
 * TeamEvent Entity.
 *
 * @property int $id
 * @property int $team_id
 * @property string $name
 * @property string $description
 * @property string $website
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $start
 * @property \Cake\I18n\FrozenTime $end
 * @property string $location_name
 * @property string $location_street
 * @property string $location_city
 * @property string $location_province
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Attendance[] $attendances
 * @property \App\Model\Entity\ActivityLog[] $attendance_reminder_emails
 * @property \App\Model\Entity\ActivityLog[] $attendance_summary_emails
 */
class TeamEvent extends Entity {

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
		'date' => 'date',
		'start' => 'start',
		'end' => 'end',
	];

}
