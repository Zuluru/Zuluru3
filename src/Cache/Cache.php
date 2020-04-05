<?php
namespace App\Cache;

use Cake\Cache\Cache as BaseCache;

class Cache extends BaseCache {
	/**
	 * Extends core ability for read-through caching, with subkeys, mainly
	 * intended for use with i18n.
	 *
	 * When called if the $subkey is not set in $config, the $callable function
	 * will be invoked. The results will then be stored into the cache config
	 * at $key, which will be an array.
	 *
	 * Examples:
	 *
	 * Using a Closure to provide data, assume `$this` is a Table object:
	 *
	 * ```
	 * $results = Cache::remember('all_articles', function () {
	 *      return $this->find('all');
	 * }, 'default', 'en');
	 * ```
	 *
	 * @param string $key The cache key to read/store data at.
	 * @param callable $callable The callable that provides data in the case when
	 *   the cache key is empty. Can be any callable type supported by your PHP.
	 * @param string $config The cache configuration to use for this operation.
	 *   Defaults to default.
	 * @return mixed If the key is found: the cached data, false if the data
	 *   missing/expired, or an error. If the key is not found: boolean of the
	 *   success of the write
	 */
	public static function remember($key, $callable, $config = 'default', $subkey = null) {
		if ($subkey === null) {
			return parent::remember($key, $callable, $config);
		}

		$existing = self::read($key, $config);
		if (empty($existing)) {
			$existing = [];
		}
		if (!array_key_exists($subkey, $existing)) {
			$existing[$subkey] = call_user_func($callable);
			self::write($key, $existing, $config);
		}

		return $existing[$subkey];
	}

}
