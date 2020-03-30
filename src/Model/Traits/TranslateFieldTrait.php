<?php
namespace App\Model\Traits;

use Cake\Core\Configure;
use Cake\I18n\I18n;

/**
 * Trait for getting a translated field from an entity, as best as possible.
 * Cached data will have all the translations loaded, but the entity itself might not have the right one.
 */
trait TranslateFieldTrait {

	public function translateField($field, $locale = null) {
		if ($locale === null) {
			$locale = I18n::getLocale();
		}
		$locale = substr($locale, 0, 2);
		$default_locale = substr(Configure::read('App.defaultLocale'), 0, 2);

		if ($this->translation($locale)->$field) {
			return $this->translation($locale)->$field;
		} else if ($locale != $default_locale && $this->translation($default_locale)->$field) {
			return $this->translation($default_locale)->$field;
		} else {
			return $this->$field;
		}
	}

}
