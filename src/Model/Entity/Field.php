<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Field Entity.
 *
 * @property int $id
 * @property string $num
 * @property bool $is_open
 * @property bool $indoor
 * @property string $surface
 * @property string $rating
 * @property int $facility_id
 * @property float $latitude
 * @property float $longitude
 * @property int $angle
 * @property int $length
 * @property int $width
 * @property int $zoom
 * @property string $layout_url
 * @property string $sport
 *
 * @property \App\Model\Entity\Facility $facility
 * @property \App\Model\Entity\GameSlot[] $game_slots
 * @property \App\Model\Entity\Note[] $notes
 *
 * @property string $long_name
 * @property string $long_code
 */
class Field extends Entity {

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
		'long_name', 'long_code',
	];

	//
	// Specifics of getting "full" field names, including facility name
	//

	// Cache results of finding the facility record
	private $_facility_record = false;

	private function _getFacilityRecord() {
		if ($this->_facility_record === false) {
			if (!isset($this->facility)) {
				$this->_facility_record = TableRegistry::get('Facilities')->get($this->facility_id);
			} else {
				$this->_facility_record = $this->facility;
			}
		}

		return $this->_facility_record;
	}

	protected function _getLongName() {
		$facility = $this->_getFacilityRecord();
		if ($facility === null) {
			return null;
		}
		return trim($facility->name . ' ' . $this->num);
	}

	protected function _getLongCode() {
		$facility = $this->_getFacilityRecord();
		if ($facility === null) {
			return null;
		}
		return trim($facility->code . ' ' . $this->num);
	}

}
