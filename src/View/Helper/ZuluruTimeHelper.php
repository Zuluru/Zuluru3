<?php

/**
 * Extend the default time helper by providing some default formats,
 * which can be overridden on a per-user basis.
 *
 * TODO: Maybe make use of $this->nice, or at least copy features from it?
 * TODO: Use the 4th parameter to $this->format for the user's timezone offset
 */
namespace App\View\Helper;

use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\View\Helper\TimeHelper;

class ZuluruTimeHelper extends TimeHelper {
	public static function time(?ChronosInterface $date) {
		if (!$date) {
			return '???';
		}

		$time_format = Configure::read('personal.time_format');
		if (empty($time_format)) {
			$time_format = current(Configure::read('options.time_formats'));
		}
		return $date->i18nFormat($time_format);
	}

	public static function date(ChronosInterface $date = null) {
		if ($date == '0000-00-00' || $date === null) {
			return __('unknown');
		} else if (strpos($date, '00-00') !== false) {
			// Some dates may only have a valid year portion
			return substr($date, 0, 4);
		} else {
			$date_format = Configure::read('personal.date_format');
		}
		if (empty($date_format)) {
			$date_format = current(Configure::read('options.date_formats'));
		}
		return $date->i18nFormat($date_format);
	}

	public static function datetime(ChronosInterface $date = null) {
		$date_format = Configure::read('personal.date_format');
		if (empty($date_format)) {
			$date_format = current(Configure::read('options.date_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty($time_format)) {
			$time_format = current(Configure::read('options.time_formats'));
		}
		return $date->i18nFormat("$date_format $time_format");
	}

	public static function day(ChronosInterface $date = null) {
		$day_format = Configure::read('personal.day_format');
		if (empty($day_format)) {
			$day_format = current(Configure::read('options.day_formats'));
		}
		return $date->i18nFormat($day_format);
	}

	public static function fulldate(ChronosInterface $date = null) {
		$day_format = Configure::read('personal.day_format');
		if (empty($day_format)) {
			$day_format = current(Configure::read('options.day_formats'));
		}
		return $date->i18nFormat("$day_format, yyyy");
	}

	public static function fulldatetime(ChronosInterface $date = null) {
		$day_format = Configure::read('personal.day_format');
		if (empty($day_format)) {
			$day_format = current(Configure::read('options.day_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty($time_format)) {
			$time_format = current(Configure::read('options.time_formats'));
		}
		return $date->i18nFormat("$day_format, yyyy $time_format");
	}

	public static function dateRange(ChronosInterface $start, ChronosInterface $end) {
		// Figure out how best to display the date(s)
		$single_year = ($start->year == $end->year);
		$entire_month = ($start->day == 1 && $end === $end->endOfMonth());

		if ($start === $end) {
			return $start->i18nFormat('MMMM d, yyyy');
		} else if ($start->month == $end->month) {
			if ($entire_month) {
				return $start->i18nFormat('MMMM yyyy');
			} else {
				return __('{0}-{1}', $start->i18nFormat('MMMM d'), $end->i18nFormat('d, yyyy'));
			}
		} else if ($entire_month) {
			if ($single_year) {
				$start_month = $start->i18nFormat('MMMM');
			} else {
				$start_month = $start->i18nFormat('MMMM yyyy');
			}
			return __('{0} to {1}', $start_month, $end->i18nFormat('MMMM yyyy'));
		} else {
			if ($single_year) {
				$start_date = $start->i18nFormat('MMMM d');
			} else {
				$start_date = $start->i18nFormat('MMMM d, yyyy');
			}
			return __('{0} to {1}', $start_date, $end->i18nFormat('MMMM d, yyyy'));
		}
	}

	public static function timeRange($obj) {
		return __('{0}-{1}', self::time($obj->start_time), self::time($obj->end_time));
	}

	public static function dateTimeRange($obj) {
		return __('{0}, {1}-{2}', self::date($obj->start_time), self::time($obj->start_time), self::time($obj->end_time));
	}

	public static function iCal(ChronosInterface $time) {
		return $time->setTimezone('UTC')->i18nFormat("yyyyMMdd'T'HHmmss'Z'");
	}

	public static function iCalDateTimeRange($obj) {
		return __('{0} {1} to {2}', $obj->start_time->i18nFormat('EEE MMM d yyyy'), self::time($obj->start_time), self::time($obj->end_time));
	}

}
