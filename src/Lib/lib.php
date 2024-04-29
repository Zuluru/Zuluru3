<?php
/**
 * Library functions that don't fit anywhere else can go here.
 */
namespace App\Lib;

use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use CommerceGuys\Intl\Country\CountryRepository;
use App\Controller\AppController;

function countryCode($data) {
	// Get the country from wherever we can
	if (is_a($data, 'Cake\ORM\Entity')) {
		if ($data->has('country')) {
			$country = $data->country;
		} else if ($data->has('addr_country')) {
			$country = $data->addr_country;
		} else if ($data->has('location_country')) {
			$country = $data->location_country;
		}
	} else {
		if (!empty($data['country'])) {
			$country = $data['country'];
		} else if (!empty($data['addr_country'])) {
			$country = $data['addr_country'];
		} else if (!empty($data['location_country'])) {
			$country = $data['location_country'];
		}
	}

	if (empty($country)) {
		$country = Configure::read('organization.country');
	}
	if (empty($country)) {
		return null;
	}

	// Convert to country code
	$countryRepository = new CountryRepository;
	$countries = array_flip($countryRepository->getList());
	return $countries[$country];
}

/**
 * Calculate local sunset time for a timestamp, using system-wide location.
 * @param mixed $date
 * @return bool|string
 */
function local_sunset_for_date(ChronosInterface $date) {
	/*
	 * value of 90 degrees 50 minutes is the angle at which
	 * the sun is below the horizon.  This is the official
	 * sunset time. Do not use "civil twilight" zenith
	 * value of 96 degrees. It's normally about 30 minutes
	 * later in the evening than official sunset, and there
	 * is some light until then, but it's too dark for safe
	 * play.
	 */
	$zenith = 90 + (50/60);

	$lat = (float) Configure::read('organization.latitude');
	$long = (float) Configure::read('organization.longitude');

	// The date comes in as midnight, UTC. If we use that, it will often find us the time
	// of sunset on the day before. Make a new time that's noon local time, and use that.
	$noon = FrozenTime::create($date->year, $date->month, $date->day, 12, 0, 0, Configure::read('App.timezone.name'));
	$sunset = date_sunset($noon->toUnixString(), SUNFUNCS_RET_TIMESTAMP, $lat, $long, $zenith);

	// Round down to nearest 5 minutes, and return a new Time object with that timestamp.
	$round = 5 * MINUTE;
	$sunset = floor($sunset / $round) * $round;
	return (new FrozenTime($sunset))->setTimezone(Configure::read('App.timezone.name'));
}

/**
 * @return array list of seasons that should be considered "current".
 */
function seasons() {
	$query = TableRegistry::get('Leagues')->find();
	return $query->find('list', [
			'keyField' => 'season',
			'valueField' => 'open_year',
		])
		->select([
			'season',
			'open_year' => $query->func()->year(['open' => 'identifier'])
		])
		->where(['is_open' => true])
		->toArray();
}

function array_transpose($array, $selectKey = false) {
	if (!is_array($array)) return false;
	$return = [];
	foreach($array as $key => $value) {
		if (!is_array($value)) return $array;
		if ($selectKey) {
			if (isset($value[$selectKey])) $return[] = $value[$selectKey];
		} else {
			foreach ($value as $key2 => $value2) {
				$return[$key2][$key] = $value2;
			}
		}
	}
	return $return;
}

function no_blank(array $array): array {
	$key = array_search(null, $array, true);
	if ($key !== false) {
		unset($array[$key]);
	}

	$key = array_search('', $array, true);
	if ($key !== false) {
		unset($array[$key]);
	}

	return $array;
}

function fake_id() {
	$unused_id = Configure::read ('unused_id');
	if (! $unused_id) {
		$unused_id = MIN_FAKE_ID;
	} else {
		++ $unused_id;
	}
	Configure::write('unused_id', $unused_id);
	return $unused_id;
}

function ical_encode($text) {
	$text = strtr($text, [
		'\\' => '\\\\',
		',' => '\\,',
		';' => '\\;',
	]);
	return $text;
}

// Base 64 encode a string for easy URL manipulation, trim any = from the end
function base64_url_encode($value) {
	return trim(base64_encode($value), '=');
}

function base64_url_decode($value) {
	// Base 64 input must have a length that's a multiple of 4, add = to pad it out
	while (strlen($value) % 4) {
		$value .= '=';
	}

	// Encoding can include + signs, which get converted to spaces. Put them back...
	$value = str_replace(' ', '+', $value);

	// Base 64 decode to recover the original input
	return base64_decode($value);
}

class contextSortHelper {
	private $value_compare_func;
	private $context;

	public function __construct(callable $value_compare_func, array $context) {
		$this->value_compare_func = $value_compare_func;
		$this->context = $context;
	}

	public function sort($a, $b) {
		$func = $this->value_compare_func;
		return $func($a, $b, $this->context);
	}
}

function context_usort(array &$array, callable $value_compare_func, array $context) {
	$helper = new contextSortHelper($value_compare_func, $context);
	usort($array, [$helper, 'sort']);
}

function context_uasort(array &$array, callable $value_compare_func, array $context) {
	$helper = new contextSortHelper($value_compare_func, $context);
	uasort($array, [$helper, 'sort']);
}

function context_uksort(array &$array, callable $value_compare_func, array $context) {
	$helper = new contextSortHelper($value_compare_func, $context);
	uksort($array, [$helper, 'sort']);
}

function csvFields($people, $fields, $this_is_admin) {
	$header1 = $header2 = [];

	// Skip fields that are all blank or disabled
	$player_fields = $fields;
	foreach ($player_fields as $field => $name) {
		// We may be passed simple true/false values to include or exclude headers in preset places
		if ($name === true) {
			$header2[] = $field;
		} else if ($name === false) {
			unset($player_fields[$field]);
			unset($fields[$field]);
			continue;
		}

		$short_field = str_replace('alternate_', '', $field);
		if (strpos($short_field, 'addr_') !== false && !$this_is_admin) {
			$include = false;
		} else if ($short_field == 'email') {
			$include = true;
		} else if ($short_field == 'work_ext') {
			$include = Configure::read('profile.work_phone');
		} else if ($short_field == 'roster_designation') {
			$include = (Configure::read('gender.column') == 'roster_designation');
		} else {
			$include = Configure::read("profile.$short_field");
		}
		if ($include) {
			if ($people->some(function ($person) use ($name, $field) {
				if (is_array($name)) {
					$model = $name['model'];
					return $person->has($model) && collection($person->$model)->some(function ($record) use ($field) { return !empty($record->$field); });
				} else {
					return !empty($person->$field);
				}
			})) {
				if (is_array($name)) {
					$name = $name['name'];
				}
				$header2[] = $name;
			} else {
				unset($player_fields[$field]);
			}
		} else {
			// Disabled fields are disabled for players and relatives
			unset($fields[$field]);
			unset($player_fields[$field]);
		}
	}

	// Check if we need to include relative contact info
	$relative_count = 0;
	foreach ($people as $person) {
		if (empty($person->user_id) || AppController::_isChild($person)) {
			$relative_count = max($relative_count, count($person->related));
		}
	}
	if ($relative_count > 0) {
		$header1 = array_fill(0, count($header2), '');
		$contact_fields = $fields;
		foreach (['gender', 'roster_designation', 'birthdate', 'height', 'skill_level', 'shirt_size'] as $field) {
			unset($contact_fields[$field]);
		}
		$contact_fields = array_fill(0, $relative_count, $contact_fields);

		for ($i = 0; $i < $relative_count; ++$i) {
			$relatives = $people->filter(function ($person) use ($i) {
				return count($person->related) > $i;
			})->extract("related.$i");

			foreach ($contact_fields[$i] as $field => $name) {
				if ($name === true) {
					$header2[] = $field;
				} else if ($relatives->some(function ($person) use ($name, $field) {
					if (is_array($name)) {
						$model = $name['model'];
						return $person->has($model) && collection($person->$model)->some(function ($record) use ($field) { return !empty($record->$field); });
					} else {
						return !empty($person->$field);
					}
				})) {
					if (is_array($name)) {
						$name = $name['name'];
					}
					$header2[] = $name;
				} else {
					unset($contact_fields[$i][$field]);
				}
			}

			if (!empty($contact_fields[$i])) {
				$header1[] = __('Contact {0}', $i + 1);
				$header1 = array_merge($header1, array_fill(0, count($contact_fields[$i]) - 1, ''));
			}
		}
	} else {
		$contact_fields = [];
	}

	return [$header1, $header2, $player_fields, $contact_fields];
}

// Helper functions for formatting data
function format_date(ChronosInterface $data = null, $ths) {
	if (empty($data) || $data->year == 0) {
		return __('unknown');
	} else if (Configure::read('feature.birth_year_only')) {
		return $data->year;
	} else {
		return $ths->Time->date($data);
	}
}

function format_height($data) {
	if (!empty($data)) {
		return $data . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm') : __('inches'));
	}
}

function format_groups($data) {
	$groups = collection($data)->extract('name')->toArray();
	if (empty($groups)) {
		return __('None');
	} else {
		return implode(', ', $groups);
	}
}
