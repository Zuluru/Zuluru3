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
	echo $this->Form->control($field, ['type' => 'textarea', 'label' => $label, 'cols' => 60, 'rows' => 5, 'secure' => $secure]);
}
