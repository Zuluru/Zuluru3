<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

echo $this->Form->control('membership_begins', [
	'type' => 'date',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => true,
	'help' => __('First date that this registration will confer membership for (e.g. beginning of the membership year).'),
	'secure' => false,
]);

echo $this->Form->control('membership_ends', [
	'type' => 'date',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => true,
	'help' => __('Last date that this registration will confer membership for (e.g. end of the membership year).'),
	'secure' => false,
]);

echo $this->Form->control('membership_type', [
	'options' => Configure::read('options.membership_types'),
	'empty' => '---',
	'hide_single' => true,
	'help' => __('Different membership types may come with limitations (e.g. play on a limited number of teams).'),
	'secure' => false,
]);

if ($this->Form->hasFormProtector()) {
	$this->Form->unlockField('membership_begins');
	$this->Form->unlockField('membership_ends');
	$this->Form->unlockField('membership_type');
}
