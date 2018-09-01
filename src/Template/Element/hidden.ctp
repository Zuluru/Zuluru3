<?php
// Output a block with hidden fields for all of the items in the provided array.
if (isset($model)) {
	$model .= '.';
} else {
	$model = '';
}

if (is_a($fields, 'Cake\ORM\Entity')) {
	$fields = $fields->toArray();
}

foreach ($fields as $field => $values) {
	if (is_array($values)) {
		echo $this->element('hidden', ['model' => $model . $field, 'fields' => $values]);
	} else {
		echo $this->Form->hidden($model . $field, ['value' => $values]);
		// TODO: Deal with CakePHP bug where numeric-indexed hidden fields get black-holed
		// Reference discussion in Template/Schedules/date.ctp
		// Only occurrences are in administrative-type things (game slot creation, schedule
		// creation/editing), so not a serious concern at this time.
		if (is_numeric($field)) {
			$this->Form->unlockField(rtrim($model, '.'));
		}
	}
}
