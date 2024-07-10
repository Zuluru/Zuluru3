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
		return $this->_time('start', 0, 0);
	}

	public function _getEndTime() {
		$end = $this->_time('end', 23, 59);
		if ($end < $this->start_time) {
			return $end->addDays(1);
		}
		return $end;
	}

	private function _time(string $which, int $hour, int $minute) {
		return $this->combine($this->{$this->_dateTimeCombinatorFields['date']}, $this->{$this->_dateTimeCombinatorFields[$which]}, $hour, $minute);
	}

	public static function combine(?ChronosInterface $date = null, ?ChronosInterface $time = null, int $hour, int $minute) {
		if ($date === null) {
			return null;
		}
		if ($time === null) {
			return FrozenTime::create($date->year, $date->month, $date->day, $hour, $minute, 0, Configure::read('App.timezone.name'));
		}
		return FrozenTime::create($date->year, $date->month, $date->day, $time->hour, $time->minute, $time->second, Configure::read('App.timezone.name'));
	}

}
