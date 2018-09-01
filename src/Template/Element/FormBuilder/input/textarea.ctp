<?php
if (!empty($preview)) {
	echo $this->Html->tag('label', $label);
} else {
	echo $this->Form->input($field, ['type' => 'textarea', 'label' => $label, 'cols' => 60, 'rows' => 5, 'secure' => $secure]);
}
