<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Collection\Collection;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * SelectorHelper class
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\TextHelper $Text
 * @property \ZuluruJquery\View\Helper\JqueryHelper $Jquery
 */
class SelectorHelper extends Helper {
	public $helpers = ['Html', 'Form', 'Text', 'ZuluruJquery.Jquery'];

	public function selector(string $title, array $options, bool $include_empty = true, array $ajax_data = []): string {
		if (count($options) <= 1) {
			return '';
		}

		$id = strtolower(Text::slug($title, '_'));
		$new_options = [];
		foreach ($options as $option) {
			$value = $option['value'];
			$new_options[] = [
				'value' => strtolower(Text::slug($value, '_')),
				'text' => $value,
				'data-ids' => $option['ids'],
			];
		}

		$input_options = [
			'id' => $id,
			'label' => __($title) . ':',
			'options' => $new_options,
			'empty' => $include_empty ? __('Show All') : false,
		];

		if (!empty($ajax_data)) {
			return $this->Html->tag('span',
				$this->Jquery->ajaxInput($id, $ajax_data, $input_options),
				['class' => 'selector']
			);
		}

		return $this->Html->tag('form',
			$this->Html->tag('span',
				$this->Form->control($id, $input_options),
				['class' => 'selector']
			),
			['class' => 'selector form-inline']
		);
	}

	public function extractOptions(iterable $items, ?callable $extractor, $value_field, $key_field = null, $id_field = 'id'): array {
		$options = $this->extractOptionsUnsorted($items, $extractor, $value_field, $key_field, $id_field);
		ksort($options);

		return $options;
	}

	public function extractOptionsUnsorted(iterable $items, ?callable $extractor, $value_field, $key_field = null, $id_field = 'id'): array {
		$options = [];

		foreach ($items as $item) {
			foreach ($this->subitems($item, $extractor) as $sub_item) {
				$value = $this->value($sub_item, $value_field);
				if (empty($value)) {
					continue;
				}

				if (!isset($key_field)) {
					$key = $value;
				} else {
					$key = $sub_item->$key_field;
				}

				$id = $item->$id_field;

				if (!array_key_exists($key, $options)) {
					$options[$key] = ['value' => $value, 'ids' => []];
				}

				$options[$key]['ids'][$id] = $id;
			}
		}

		return $options;
	}

	private function subitems(EntityInterface $item, ?callable $extractor): array {
		if (is_null($extractor)) {
			return [$item];
		}

		$extracted = $extractor($item);
		if (is_null($extracted)) {
			return [];
		}
		if (is_array($extracted)) {
			return $extracted;
		}
		if ($extracted instanceof Collection) {
			return $extracted->toArray();
		}

		return [$extracted];
	}

	private function value(object $item, $value) {
		if (is_string($value)) {
			return (string)$item->$value;
		}

		if (is_callable($value)) {
			return $value($item);
		}

		throw new \Exception('Unexpected value type');
	}

	public function radioSelector(string $slug, string $title, array $options): string {
		// Some things that we might group by don't have the thing we're grouping by,
		// e.g. not all events have sports or seasons. Eliminate any blank option.
		if (current($options) == '') {
			array_shift($options);
		}

		if (empty($options)) {
			return '';
		}

		$name = strtolower(Text::slug($title, '_'));
		$new_options = [];
		foreach ($options as $option) {
			$value = $option['value'];
			$classes = collection($option['ids'])->extract(function (int $id) { return "select_radio_id_{$id} option_radio_id_{$id}"; })->toArray();
			$class = implode(' ', $classes);

			$new_options[] = [
				'value' => strtolower(Text::slug($value, '_')),
				'text' => $value,
				'class' => $class,
				'data-ids' => $option['ids'],
			];
		}

		$input = $this->Form->control($name, [
			'id' => "{$slug}_{$name}",
			'label' => false,
			'type' => 'radio',
			'options' => $new_options,
			'hiddenField' => false,
		]);

		return $this->Html->tag('form',
			$this->Html->tag('span', $input, ['class' => 'selector']),
			['class' => 'selector']
		);
	}
}
