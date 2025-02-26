<?php
/**
 * @var \App\View\AppView $this
 * @var string $field
 * @var string $label
 * @var array $options
 * @var bool $secure
 */

$answers = [];
$default = null;
foreach ($options as $key => $option) {
	$answers[(string)$option['value']] = $option['text'];
	if (array_key_exists('default', $option) && $option['default'])
		$default = $option['value'];
}

echo $this->Html->tag('label', $label);
echo $this->Form->control($field, ['type' => 'radio', 'label' => false, 'options' => $answers, 'default' => $default, 'help' => $desc, 'secure' => $secure]);
