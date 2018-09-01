<?php
namespace App\Validation;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat;
use CommerceGuys\Addressing\Validator\Constraints\Country;
use Symfony\Component\Validator\Validation;

/**
 * Validation class
 *
 * Provides custom validation functions
 */
class Intl extends \Cake\Validation\Validation {
	public static function postal($check, $context) {
		$countryCode = \App\Lib\countryCode($context['data']);

		$address = new Address();
		$address = $address->withCountryCode($countryCode)->withPostalCode($check);

		// Validate the country code, then validate the rest of the address.
		$validator = Validation::createValidator();
		$violations = $validator->validate($address->getCountryCode(), new Country());
		if (!$violations->count()) {
			$violations = $validator->validate($address, new AddressFormat());
			foreach ($violations as $violation) {
				if ($violation->getPropertyPath() == '[postalCode]') {
					return false;
				}
			}
		}

		return true;
	}

	public static function phone($check, $context) {
		$countryCode = \App\Lib\countryCode($context['data']);
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		try {
			$number = $phoneUtil->parse($check, $countryCode);
		} catch (\libphonenumber\NumberParseException $e) {
			return false;
		}
		return $phoneUtil->isValidNumber($number);
	}

}
