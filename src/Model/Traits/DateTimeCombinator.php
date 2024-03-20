<?php
namespace App\Model\Traits;

use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

/**
 * Trait for combining date and time fields into an actual timestamp with correct timezone, etc.
 *
 * @property \Cake\I18n\FrozenTime $start_time
 * @property \Cake\I18n\FrozenTime $end_time
 */
trait DateTimeCombinator {

	public function _getStartTime() {
		return $this->_time('start');
	}

	public function _getEndTime() {
		$end = $this->_time('end');
		if ($end < $this->start_time) {
			return $end->addDays(1);
		}
		return $end;
	}

	private function _time($which) {
		return $this->combine($this->{$this->_dateTimeCombinatorFields['date']}, $this->{$this->_dateTimeCombinatorFields[$which]});
	}

	public static function combine(ChronosInterface $date = null, ChronosInterface $time = null) {
		if ($date === null || $time === null) {
			return null;
		}
		return FrozenTime::create($date->year, $date->month, $date->day, $time->hour, $time->minute, $time->second, Configure::read('App.timezone.name'));
	}

}
