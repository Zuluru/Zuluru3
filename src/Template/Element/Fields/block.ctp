<?php
/**
 * @type $field \App\Model\Entity\Field
 * @type $options mixed[]
 * @type $display_field string
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
