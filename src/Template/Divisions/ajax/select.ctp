<?php
/**
 * @var \App\View\AppView $this
 */

if (empty($divisions)) {
	echo __('No divisions operate on the selected night.');
} else {
	// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
	$this->Form->create(false, ['align' => 'horizontal']);

	echo $this->Form->input('divisions._ids', [
		'label' => false,
		'multiple' => 'checkbox',
		'hiddenField' => false,
	]);
}
