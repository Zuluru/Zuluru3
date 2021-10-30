<?php
if (isset($price)) {
	// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
	$this->Form->create(false, ['align' => 'horizontal']);

	echo $this->element('Registrations/register_payment_fields', [
		'price' => $price,
		'registration' => (!empty($price->registration) ? $price->registration : null),
	]);

	if ($price->has('canRegister')) {
		if ($price->canRegister['allowed']) {
			echo $this->Html->scriptBlock("zjQuery(':button[type=\"submit\"]').prop('disabled', false);");
		} else {
			echo $this->Html->scriptBlock("zjQuery(':button[type=\"submit\"]').prop('disabled', true);");
		}
	}
} else {
	echo $this->Html->scriptBlock("zjQuery(':button[type=\"submit\"]').prop('disabled', true);");
}
