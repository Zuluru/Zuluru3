<?php
foreach ($options as $option) {
	if (is_array($option) && array_key_exists('value', $option) && $option['value'] == $answer) {
		echo $this->Html->para(null, __($option['text']));
	}
}
