<?php

/**
 * A behavior that reformats certain fields
 */
namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

class FormatterBehavior extends Behavior {
	protected $_defaultConfig = [];

	public function format(Entity $entity) {
		$config = $this->config();
		$countryCode = \App\Lib\countryCode($entity);
		foreach ($config['fields'] as $field => $formatter) {
			if ($entity->has($field) && !empty($entity->$field)) {
				if (is_callable($formatter) || function_exists($formatter)) {
					$new = $formatter($entity->$field, $countryCode);
				} else if (method_exists($this->_table, $formatter)) {
					$new = $this->_table->$formatter($entity->$field, $countryCode);
				} else if (method_exists($this, $formatter)) {
					$new = $this->$formatter($entity->$field, $countryCode);
				} else {
					trigger_error("Formatter $formatter not found for field $field in model " . $this->_table->alias(), E_USER_ERROR);
				}
				if ($new != $entity->$field) {
					$entity->set($field, $new);
				}
			}
		}
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @return void
	 */
	public function beforeSave(CakeEvent $cakeEvent, Entity $entity) {
		$this->format($entity);
	}

	// TODO: Incorporate a comprehensive third-party library
	public function postal_format($val, $country) {
		$clean = '';
		$val = strtoupper($val);
		for ($i = 0; $i < strlen($val); ++$i) {
			if (ctype_alnum($val[$i])) {
				$clean .= $val[$i];
			}
		}

		// Reference: https://en.wikipedia.org/wiki/List_of_postal_codes
		$params = [
			'AI' => ['length' => 6, 'at' => 2, 'divider' => '-'],
			'AX' => ['length' => 7, 'at' => 2, 'divider' => '-'],
			'BR' => ['length' => 8, 'at' => 5, 'divider' => '-'],
			'CA' => ['length' => 6, 'at' => 3, 'divider' => ' '],
			'CZ' => ['length' => 5, 'at' => 3, 'divider' => ' '],
			'GB' => ['length' => [5,6,7], 'at' => -3, 'divider' => ' '],
				'GG' => ['length' => [6,7], 'at' => -3, 'divider' => ' '],
				'IM' => ['length' => [6,7], 'at' => -3, 'divider' => ' '],
				'JE' => ['length' => [6,7], 'at' => -3, 'divider' => ' '],
				'AC' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'AQ' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'FK' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'GI' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'GS' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'IO' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'PN' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'SH' => ['length' => 7, 'at' => 4, 'divider' => ' '],
				'TC' => ['length' => 7, 'at' => 4, 'divider' => ' '],
			'GR' => ['length' => 5, 'at' => 3, 'divider' => ' '],
			'JP' => ['length' => 7, 'at' => 3, 'divider' => '-'],
			'KY' => ['length' => 7, 'at' => 3, 'divider' => '-'],
			'LB' => ['length' => 8, 'at' => 4, 'divider' => ' '],
			'LT' => ['length' => 7, 'at' => 2, 'divider' => '-'],
			'LV' => ['length' => 6, 'at' => 2, 'divider' => '-'],
			'MT' => ['length' => 7, 'at' => 3, 'divider' => ' '],
			'PE' => ['length' => 6, 'at' => 2, 'divider' => ' '],
			'PL' => ['length' => 5, 'at' => 2, 'divider' => '-'],
			'PT' => ['length' => 7, 'at' => 4, 'divider' => '-'],
			'SA' => ['length' => 9, 'at' => 5, 'divider' => '-'],
			'SE' => ['length' => 5, 'at' => 3, 'divider' => ' '],
			'SI' => ['length' => 6, 'at' => 2, 'divider' => '-'],
			'SK' => ['length' => 5, 'at' => 3, 'divider' => ' '],
			'SO' => ['length' => 7, 'at' => 2, 'divider' => ' '],
			'TW' => ['length' => 5, 'at' => 3, 'divider' => '-'],
			'US' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'AS' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'FM' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'GU' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'MH' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'MP' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'PR' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'PW' => ['length' => 9, 'at' => 5, 'divider' => '-'],
				'VI' => ['length' => 9, 'at' => 5, 'divider' => '-'],
			'VE' => ['length' => 5, 'at' => 4, 'divider' => '-'],
		];

		if (array_key_exists($country, $params)) {
			$p = $params[$country];

			if (!is_array($p['length'])) {
				$p['length'] = [$p['length']];
			}
			if (in_array(strlen($clean), $p['length'])) {
				return substr($clean, 0, $p['at']) . $p['divider'] . substr($clean, $p['at']);
			}
		}

		// Anything else is just a blob of characters
		return $clean;
	}

	// Reformat a phone number into a standard format.
	// Returns the original input if the input is not something we recognize as being a phone number.
	// This function explicitly does *not* handle extensions.
	public function phone_format($num, $country) {
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		try {
			$numberProto = $phoneUtil->parse($num, $country);
		} catch (\libphonenumber\NumberParseException $e) {
			// How did an invalid number get through validation? Just return what we got...
			return $num;
		}

		return $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL);
	}

	// Reformat a name to Proper Case
	public function proper_case_format($name, $country) {
		// If the input already has both upper and lower case letters,
		// we'll assume that the user entered it correctly.
		if (preg_match('/[A-Z]/', $name) && preg_match('/[a-z]/', $name)) {
			return $name;
		}

		$name = ucwords(strtolower($name), " \t\r\n\f\v.");

		// Not perfect but find common last names with mid-word uppercasing, and try to uppercase them
		$pattern='/
				(?: ^ | \\b )			# assertion: beginning of string or a word boundary
				( O\' | Ma?c | Van )	# attempt to match common surnames
				( [^\W\d_] )			# match next char; we exclude digits and _ from \w
			/x';
		return preg_replace_callback($pattern,
										function ($matches) {
												return $matches[1].strtoupper($matches[2]);
										},
									$name);
	}

}
