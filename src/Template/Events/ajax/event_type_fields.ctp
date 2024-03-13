<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EventType $event_obj
 */

use App\Core\ModuleRegistry;

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(false, ['align' => 'horizontal']);

$fields = ($event_obj->configurationFields());
foreach (ModuleRegistry::getModuleList('EventType') as $type) {
	$other = ModuleRegistry::getInstance()->load("EventType:{$type}");
	$other_fields = $other->configurationFields();
	foreach ($other_fields as $field) {
		if (!in_array($field, $fields)) {
			$this->Form->unlockField($field);
			$fields[] = $field;
		}
	}
}

echo $this->element('Registrations/configuration/' . $event_obj->configurationFieldsElement());
