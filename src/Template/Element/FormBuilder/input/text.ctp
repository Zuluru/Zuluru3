<?php
/**
 * @var \App\View\AppView $this
 * @var string $field
 * @var string $label
 * @var bool $preview
 * @var bool $secure
 */

if (!empty($preview)) {
	echo $this->Html->tag('label', $label);
} else {
	echo $this->Form->input($field, ['type' => 'text', 'label' => $label, 'size' => 60, 'secure' => $secure]);
}
