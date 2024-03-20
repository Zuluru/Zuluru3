<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\View\View;
use BootstrapUI\View\Helper\FormHelper as FormHelper;

class ZuluruFormHelper extends FormHelper {
	public $helpers = ['Url', 'Html' => ['className' => 'ZuluruHtml']];

	/**
	 * An array of optional fields whose values must match if they are included.
	 * This is for things like IDs of records that might be removed from the
	 * POSTed data by JavaScript.
	 *
	 * @var array
	 */
	protected $_optionalFields = [];

	public function __construct(View $View, array $config = []) {
		// Set the date widget to month-day-year instead of year-month-day. TODOSECOND: Locale specific setting.
		$this->_templateSet['horizontal'] = array_merge($this->_templateSet['horizontal'], [
			'dateWidget' => '{{month}}{{day}}{{year}}{{hour}}{{minute}}{{second}}{{meridian}}',
		]);

		parent::__construct($View, $config);
	}

	public function create($context = null, array $options = []): string {
		// TODOLATER: Remove this once we're done with validation testing
		$options['novalidate'] = true;

		return parent::create($context, $options);
	}

	/**
	 * Extend the default control function by allowing for use of a hidden
	 * field instead of a select, if there's only one option.
	 * Also, add popup help link, if available.
	 * TODOLATER: Deal with new data format (_joinData, etc.): we don't need it for anything right now?
	 */
	public function control(string $fieldName, array $options = []): string {
		$options += ['secure' => true];

		// Split into model and field name
		if (strpos($fieldName, '.') !== false) {
			$parts = explode('.', $fieldName);

			// The field name should be the last thing
			$shortFieldName = array_pop($parts);

			// The model name should be the next last non-numeric thing
			do {
				$model = array_pop($parts);
			} while (is_numeric($model));
			$model = Inflector::tableize($model);
		} else {
			$model = Inflector::tableize($this->getView()->getRequest()->getParam('controller'));
			$shortFieldName = $fieldName;
		}
		$value = $this->context()->val($fieldName);

		// If no options were provided, check if there's some configured
		// TODO: Are there more places that we can use this feature?
		if (!array_key_exists('type', $options) && !array_key_exists('options', $options) &&
			Configure::read("options.$model.$shortFieldName") !== null)
		{
			$options['options'] = Configure::read("options.$model.$shortFieldName");
		}

		if (!empty($options['hide_single'])) {
			unset($options['hide_single']);
			$is_select = (array_key_exists('type', $options) && $options['type'] == 'select') ||
				(!array_key_exists('type', $options));

			if ($is_select) {
				// This is normally done by the parent form helper, but we need
				// to know what the options are now, so that we can hide it if
				// necessary.
				$options['type'] = 'select';
				$options = $this->_optionsOptions($fieldName, $options);

				if (array_key_exists('options', $options) && count($options['options']) == 1) {
					$value = current(array_keys($options['options']));
					if (!is_array($options['options'][$value])) {
						return parent::hidden($fieldName, ['value' => $value, 'secure' => $options['secure']]);
					}
				}
			}
		}

		// Add some default settings
		if (isset($options['type'])) {
			$type = $options['type'];
		} else {
			$type = $this->_inputType($fieldName, $options);
		}
		if ($type == 'time' && !array_key_exists('timeFormat', $options)) {
			$options['timeFormat'] = 12;
		}

		// Check if we need to allow a larger date range
		if (!empty($options['looseYears']) && !empty($options['minYear'])) {
			if (!empty($value)) {
				if (is_array($value)) {
					$year = $value['year'];
				} else {
					$year = $value->year;
				}
				// Account for null values in the database; we don't really want to give all options back to Roman times...
				if ($year > 0) {
					$options['minYear'] = min($options['minYear'], $year - 1);
					$options['maxYear'] = max($options['maxYear'], $year + 1);
				}
			}
		}

		// Check if there's online help for this field
		$duplicate = !empty($options['duplicate_help']);
		unset($options['duplicate_help']);
		$help_file = ROOT . DS . 'templates' . DS . 'element' . DS . 'Help' . DS . $model . DS . 'edit' . DS . strtolower($shortFieldName) . '.php';
		if (file_exists($help_file)) {
			$help = ' ' . $this->Html->help(['action' => $model, 'edit', strtolower($shortFieldName)], $duplicate);

			// If we have some help text, add this at the end of it.
			if (array_key_exists('help', $options)) {
				if ($options['help'] !== false) {
					$options['help'] .= ' ' . $help;
				}
			} else {
				$options['help'] = $help;
			}
		}

		return parent::control($fieldName, $options);
	}

	public function iconPostLink($img, $url = null, array $imgOptions = [], array $linkOptions = []): string {
		if (array_key_exists('class', $linkOptions)) {
			if (is_array($linkOptions['class'])) {
				$linkOptions[] = 'icon';
			} else {
				$linkOptions .= ' icon';
			}
		} else {
			$linkOptions['class'] = 'icon';
		}

		return $this->postLink($this->Html->iconImg($img, $imgOptions),
			$url, array_merge(['escape' => false], $linkOptions));
	}

}
