<?php
namespace App\PasswordHasher;

use Authentication\PasswordHasher\LegacyPasswordHasher;

trait HasherTrait {
	private $_hasher = null;

	private function initHasher(): void {
		if (!$this->_hasher) {
			// The hash that bcrypt creates is not good for emailed links:
			// it is long and contains non-letter characters, which make it
			// more likely to be broken across lines, causing support calls.
			// sha1 is much better than md5, but still good for links.
			$this->_hasher = new LegacyPasswordHasher(['hashType' => 'sha1']);
		}
	}

	/**
	 * Generates hash of the input.
	 *
	 * @param string|array $input Plain text to hash.
	 * @return string Input hash
	 */
	public function _makeHash($input): string {
		$this->initHasher();
		if (is_array($input)) {
			$input = implode(':', array_map(function ($value) {
				if (is_a($value, \Cake\Chronos\ChronosInterface::class)) {
					// Be absolutely certain that dates and times are always formatted the same, regardless of locale
					return $value->toDateTimeString();
				}
				return $value;
			}, $input));
		}
		$hash = $this->_hasher->hash($input);
		return strtr($hash, ['$' => '_', '/' => '-']);
	}

	/**
	 * Verifies that the provided input corresponds to its hashed version
	 *
	 * @param string|array $input Plain text to hash.
	 * @param string $hash Existing hash.
	 * @return bool True if hashes match else false.
	 */
	public function _checkHash($input, $hash): bool {
		$this->initHasher();
		if (is_array($input)) {
			$input = implode(':', array_map(function ($value) {
				if (is_a($value, \Cake\Chronos\ChronosInterface::class)) {
					// Be absolutely certain that dates and times are always formatted the same, regardless of locale
					return $value->toDateTimeString();
				}
				return $value;
			}, $input));
		}
		$hash = strtr($hash, ['_' => '$', '-' => '/']);
		return $this->_hasher->check($input, $hash);
	}

}
