<?php
/**
 * @var \App\View\AppView $this
 */

if (empty($divisions)) {
	echo __('No divisions operate on the selected night.');
} else {
	// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
	$this->form->create(null, ['align' => 'horizontal']);

	echo $this->Form->control('divisions._ids', [
		'label' => false,
		'multiple' => 'checkbox',
		'hiddenField' => false,
	]);
}
