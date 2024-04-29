<?php
/**
 * @var \App\View\AppView $this
 * @var array $fields
 */

// On the division edit page, we don't need the model name included. Anywhere else (e.g. league edit), we do.
$prefix = (isset($index) ? "divisions.$index." : '');

foreach ($fields as $field => $options) {
	echo $this->Form->control("$prefix$field", array_merge(['secure' => false], $options));
	if ($this->Form->hasFormProtector()) {
		$this->Form->unlockField("$prefix$field");
	}
}

// We also need to unlock any other possible fields that might be included by any other schedule type
if (isset($unlock_fields) && $this->Form->hasFormProtector()) {
	foreach ($unlock_fields as $field) {
		$this->Form->unlockField("$prefix$field");
	}
}
