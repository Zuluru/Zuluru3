<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Mailer\Renderer as CakeRenderer;
use Cake\Utility\Text;

class Renderer extends CakeRenderer
{
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
	 * @inheritDoc
	 */
	public function render(string $content, array $types = []): array {
		$rendered = [];
		$template = $this->viewBuilder()->getTemplate();
		if (empty($template)) {
			foreach ($types as $type) {
				$rendered[$type] = $content;
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

			$view = $this->createView();

			[$templatePlugin] = pluginSplit($view->getTemplate());
			[$layoutPlugin] = pluginSplit($view->getLayout());
			if ($templatePlugin) {
				$view->setPlugin($templatePlugin);
			} elseif ($layoutPlugin) {
				$view->setPlugin($layoutPlugin);
			}

			if ($view->get('content') === null) {
				$view->set('content', $content);
			}

			foreach ($locales as $locale) {
				I18n::setLocale($locale);
				foreach ($types as $type) {
					$view->setTemplatePath(static::TEMPLATE_FOLDER . DIRECTORY_SEPARATOR . $type);
					$view->setLayoutPath(static::TEMPLATE_FOLDER . DIRECTORY_SEPARATOR . $type);

					$render = $view->render();
					$render = str_replace(["\r\n", "\r"], "\n", $render);
					$rendered[$type] .= $this->_divider[$type] . $render;
				}
			}

			foreach ($rendered as $type => $content) {
				$view->setTemplatePath(static::TEMPLATE_FOLDER . DIRECTORY_SEPARATOR . $type);
				$view->setLayoutPath(static::TEMPLATE_FOLDER . DIRECTORY_SEPARATOR . $type);
				$rendered[$type] = $view->renderLayout($content);
			}
		} else {
			// If there's just the one locale, we can just use the standard function
			$rendered = parent::render($content, $types);
		}

		I18n::setLocale($saved_locale);
		return $rendered;
	}
}
