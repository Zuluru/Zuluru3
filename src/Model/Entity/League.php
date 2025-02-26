<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\Core\Configure;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;

/**
 * League Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $sport
 * @property string $season
 * @property \Cake\I18n\FrozenTime $open
 * @property \Cake\I18n\FrozenTime $close
 * @property bool $is_open
 * @property int $schedule_attempts
 * @property string $display_sotg
 * @property string $sotg_questions
 * @property bool $numeric_sotg
 * @property int $expected_max_score
 * @property int $affiliate_id
 * @property string $stat_tracking
 * @property string $tie_breaker
 * @property bool $carbon_flip
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Division[] $divisions
 * @property \App\Model\Entity\StatType[] $stat_types
 * @property \App\Model\Entity\Category[] $categories
 *
 * @property string $long_name
 * @property string $full_name
 * @property string $long_season
 * @property string[] $tie_breakers
 */
class League extends Entity {

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

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'long_name', 'full_name', 'long_season',
	];

	protected function _getLongName() {
		$long_name = $this->translateField('name');

		// Add the season, if it's not already part of the name
		if (!empty($this->season) && $this->season != 'None') {
			if (strpos($long_name, $this->season) === false) {
				$long_name = __($this->season) . ' ' . $long_name;
			}
		}

		// Add the sport, if there are multiple options
		if (!empty($this->sport) && count(Configure::read('options.sport')) > 1) {
			$long_name .= ' ' . Inflector::humanize(__($this->sport));
		}

		return trim($long_name);
	}

	protected function _getFullName() {
		$full_name = $this->long_name;

		// Add the year, if it's not already part of the name
		if (!empty($this->open)) {
			$year = $this->open->year;
			if (strpos($full_name, (string)$year) === false) {
				// TODO: Add closing year, if different than opening
				$full_name = $year . ' ' . $full_name;
			}
		}

		return trim($full_name);
	}

	protected function _getLongSeason() {
		if (!empty($this->open)) {
			$year = (string)$this->open->year;
			if (!empty($this->season) && $this->season != 'None') {
				$long_season = $year . ' ' . __($this->season);
			} else {
				$long_season = $year;
			}
		} else if (!empty($this->season)) {
			$long_season = __($this->season);
		} else {
			$long_season = null;
		}

		return $long_season;
	}

	protected function _getTieBreakers() {
		return explode(',', $this->tie_breaker);
	}

	public function hasSpirit() {
		if (!Configure::read('feature.spirit')) {
			return false;
		}
		return ($this->numeric_sotg || $this->sotg_questions != 'none');
	}

	public function hasCarbonFlip() {
		if (!Configure::read('scoring.carbon_flip')) {
			return false;
		}
		return $this->carbon_flip;
	}

	public function hasStats() {
		if (!Configure::read('scoring.stat_tracking')) {
			return false;
		}
		if ($this->schedule_type == 'none') {
			return false;
		}

		return ($this->stat_tracking != 'never');
	}

}
