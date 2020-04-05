<?php
$answers = [];
$default = null;
foreach ($options as $key => $option) {
	$answers[$option['value']] = $option['text'];
	if (array_key_exists('default', $option) && $option['default'])
		$default = $option['value'];
}

echo $this->Html->tag('label', $label);
echo $this->Form->input($field, ['type' => 'radio', 'label' => false, 'options' => $answers, 'default' => $default, 'help' => $desc, 'secure' => $secure]);
