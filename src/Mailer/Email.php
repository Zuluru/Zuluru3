<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Mailer\Email as CakeEmail;
use Cake\Utility\Text;

class Email extends CakeEmail {

	/**
	 * Locales to use for sending the email
	 *
	 * @var array
	 */
	protected $_locales = [];

	/**
	 * Formats to use for the "follows" blocks in different types
	 *
	 * @var array
	 */
	protected $_followsFormat = [
		'html' => "<p>%s</p>\n",
		'text' => "%s\n",
	];

	/**
	 * Dividers to use between template renderings in different types
	 *
	 * @var array
	 */
	protected $_divider = [
		'html' => "\n<hr/>\n",
		'text' => "\n------------------------------------------------------------------------\n\n",
	];

	/**
	 * Sets locales.
	 *
	 * @param array $locales Array of locales
	 * @return $this
	 */
	public function setLocales($locales) {
		$this->_locales = $locales;
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

	/**
	 * Build and set all the view properties needed to render the templated emails.
	 * If there is no template set, the $content will be returned in a hash
	 * of the text content types for the email.
	 *
	 * @param string $content The content passed in from send() in most cases.
	 * @return array The rendered content with html and text keys.
	 */
	protected function _renderTemplates($content) {
		$types = $this->_getTypes();
		$rendered = [];
		$template = $this->viewBuilder()->getTemplate();
		if (empty($template)) {
			foreach ($types as $type) {
				$rendered[$type] = $this->_encodeString($content, $this->charset);
			}

			return $rendered;
		}

		$saved_locale = I18n::getLocale();

		$locales = $this->getLocales();
		if (empty($locales)) {
			$locales = [Configure::read('App.defaultLocale')];
		}
		I18n::setLocale(current($locales));

		if (count($locales) > 1) {
			$other_locales = array_slice($locales, 1);
			$languages = [];
			foreach ($other_locales as $locale) {
				$language = substr($locale, 0, 2);
				$languages[] = Configure::read("available_translations.$language");
			}
			$follows = __n('Version in {0} follows.', 'Versions in {0} follow.', count($other_locales), Text::toList($languages));

			foreach ($types as $type) {
				$rendered[$type] = sprintf($this->_followsFormat[$type], $follows);
			}

			$View = $this->createView();

			list($templatePlugin) = pluginSplit($View->getTemplate());
			list($layoutPlugin) = pluginSplit($View->getLayout());
			if ($templatePlugin) {
				$View->setPlugin($templatePlugin);
			} elseif ($layoutPlugin) {
				$View->setPlugin($layoutPlugin);
			}

			if ($View->get('content') === null) {
				$View->set('content', $content);
			}

			foreach ($locales as $locale) {
				I18n::setLocale($locale);
				foreach ($types as $type) {
					// @todo Cake4: Just remove the setting of 'hasRendered'
					$View->hasRendered = false;
					$View->setTemplatePath('Email' . DIRECTORY_SEPARATOR . $type);
					$View->enableAutoLayout(false);

					$render = $View->render();
					$render = str_replace(["\r\n", "\r"], "\n", $render);
					$rendered[$type] .= $this->_divider[$type] . $this->_encodeString($render, $this->charset);
				}
			}

			foreach ($rendered as $type => $content) {
				$View->setLayoutPath('Email' . DIRECTORY_SEPARATOR . $type);
				$content = $View->renderLayout($content);
				$rendered[$type] = $this->_wrap($content);
				$rendered[$type] = implode("\n", $rendered[$type]);
				$rendered[$type] = rtrim($rendered[$type], "\n");
			}
		} else {
			// If there's just the one locale, we can just use the standard function
			$rendered = parent::_renderTemplates($content);
		}

		I18n::setLocale($saved_locale);
		return $rendered;
	}

}
