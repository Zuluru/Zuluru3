<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\Chronos\ChronosInterface;
use Cake\I18n\FrozenDate;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Waiver Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $text
 * @property bool $active
 * @property string $expiry_type
 * @property int $start_month
 * @property int $start_day
 * @property int $end_month
 * @property int $end_day
 * @property int $duration
 * @property int $affiliate_id
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Person[] $people
 */
class Waiver extends Entity {

	use TranslateTrait;
	use TranslateFieldTrait;

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

	public function canSign(ChronosInterface $date = null) {
		$now = FrozenDate::now();
		if (!$date) {
			$date = $now;
		}

		// You can't sign a waiver as of a date before the waiver takes effect
		list($start, $end) = $this->validRange($date);
		if ($date < $start) {
			return false;
		}

		// You can't sign a waiver more than a year in the future
		if ($date > $now->addYear()) {
			return false;
		}

		return true;
	}

	public function validRange(ChronosInterface $date = null) {
		$now = FrozenDate::now();
		if (!$date) {
			$date = $now;
		}

		switch ($this->expiry_type) {
			case 'fixed_dates':
				$start = $date->month($this->start_month)->day($this->start_day);
				$end = $date->month($this->end_month)->day($this->end_day);
				while ($end < $date) {
					$end = $end->addYear();
				}
				if ($end < $start) {
					$start = $start->subYear();
				}
				if ($start <= $date && $date <= $end) {
					return [$start, $end];
				}
				return [false, false];

			case 'elapsed_time':
				return [$now, $now->addDays($this->duration)];

			case 'event':
				return [$date, $date];

			case 'never':
				return [$date, new FrozenDate('9999-12-31')];
		}
	}

}
