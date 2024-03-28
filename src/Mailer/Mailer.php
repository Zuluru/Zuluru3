<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Mailer\Mailer as CakeMailer;

class Mailer extends CakeMailer {

	/**
	 * Locales to use for sending the email
	 *
	 * @var array
	 */
	protected $_locales = [];

	/**
	 * @inheritDoc
	 */
	public function __construct($config = null)
	{
		$this->setRenderer(new Renderer());
		parent::__construct($config);
	}

	/**
	 * Sets locales.
	 *
	 * @param array $locales Array of locales
	 * @return $this
	 */
	public function setLocales($locales) {
		$this->_locales = $locales;
		$this->getRenderer()->setLocales($locales);
		return $this;
	}

	/**
	 * Gets locales
	 *
	 * @return array
	 */
	public function getLocales() {
		return $this->_locales;
	}

	/**
	 * Sets subject.
	 *
	 * @param string|callable $subject Subject string, or a function to return the subject.
	 * @return $this
	 */
	public function setSubject($subject) {
		if (is_callable($subject)) {
			$saved_locale = I18n::getLocale();

			$locales = $this->getLocales();
			if (empty($locales)) {
				$locales = [Configure::read('App.defaultLocale')];
			}

			$subjects = [];
			foreach ($locales as $locale) {
				I18n::setLocale($locale);
				$subjects[] = call_user_func($subject);
			}

			I18n::setLocale($saved_locale);
			parent::setSubject(implode(' / ', $subjects));
		} else {
			parent::setSubject($subject);
		}

		return $this;
	}
}
