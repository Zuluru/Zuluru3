<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Datasource\EntityInterface;
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * SelectorHelper class
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \ZuluruJquery\View\Helper\JqueryHelper $Jquery
 */
class SelectorHelper extends Helper {
	public $helpers = ['Html', 'Form', 'ZuluruJquery.Jquery'];

	public function selector(array $items, string $title, $value_extractor, $key_extractor = null, $id_extractor = 'id',
		bool $sort = true, bool $grouped = false, bool $include_form = true, bool $include_empty = true, array $data = []
	): string {
		if (!isset($key_extractor)) {
			$key_extractor = $value_extractor;
		}

		$options = $this->extractOptions($items, $value_extractor, $key_extractor, $id_extractor, $sort, $grouped);

		if (count($options) <= 1) {
			return '';
		}

		$output = '';

		$id = strtolower(Text::slug($title, '_'));
		$new_options = [];
		foreach ($options as $option) {
			// TODO: Do something with the 'ids' key
			$option = $option['value'];
			$new_options[strtolower(Text::slug($option, '_'))] = $option;
		}

		$input_options = [
			'id' => $id,
			'label' => __($title) . ':',
			'options' => $new_options,
		];
		if (!isset($include_empty) || $include_empty) {
			$input_options['empty'] = __('Show All');
		}
		if (!empty($data)) {
			// Selectors might also be Ajax inputs
			$output .= $this->Jquery->ajaxInput($id, $data, $input_options);
		} else {
			$output .= $this->Form->control($id, $input_options);
		}

		$output = $this->Html->tag('span', $output, ['class' => 'selector']);

		if ($include_form) {
			$output = $this->Html->tag('form', $output, ['class' => 'selector form-inline']);
		}

		return $output;
	}

	private function extractOptions(array $items, $value_extractor, $key_extractor, $id_extractor, bool $sort = true, bool $grouped = false): array {
		$options = [];

		if ($grouped) {
			foreach ($items as $group_items) {
				// TODO: Merge the options generated with what we already have
				$new = $this->extractOptions($group_items, $value_extractor, $key_extractor, $id_extractor, $sort);
				$options = array_merge_recursive($options, $new);
				//debug(compact('new', 'options'));
			}

			return $options;
		}

		foreach ($items as $item) {
			$value = $this->extract($item, $value_extractor);
			if (empty($value)) {
				continue;
			}

			$key = $this->extract($item, $key_extractor);
			if (!array_key_exists($key, $options)) {
				$options[$key] = ['value' => $value, 'ids' => []];
			}

			$options[$key]['ids'][] = $this->extract($item, $id_extractor);
		}

		if ($sort) {
			ksort($options);
		}

		return $options;
	}

	private function extract(EntityInterface $item, $extractor) {
		if (is_string($extractor)) {
			return $item->$extractor;
		}

		if (is_callable($extractor)) {
			return $extractor($item);
		}

		throw new \Exception('Unexpected extractor type.');
	}
}
