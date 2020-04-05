<?php
/**
 * @type \App\Model\Entity\Field $field
 * @type mixed[] $options
 * @type string $display_field
 */

$id = "fields_field_{$field->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (!isset($display_field)) {
	$display_field = 'long_code';
}
echo $this->Html->link($field->$display_field,
	['controller' => 'Facilities', 'action' => 'view', 'facility' => $field->facility_id],
	$options);
