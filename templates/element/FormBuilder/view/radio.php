<?php
/**
 * @var \App\View\AppView $this
 * @var array $options
 */

foreach ($options as $option) {
	if (is_array($option) && array_key_exists('value', $option) && $option['value'] == $answer) {
		echo $this->Html->para(null, __($option['text']));
	}
}
