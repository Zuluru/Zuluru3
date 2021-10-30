<?php
use App\Model\Table\PricesTable;

if (isset($price) && !empty($price->canRegister) && empty($for_edit)) {
	echo $this->element('messages', ['messages' => [$price->canRegister]]);
}

if (isset($price) && ($price->canRegister['allowed'] || !empty($for_edit))) {
	$options = [];
	if ($price->allow_deposit) {
		if ($price->fixed_deposit) {
			$options['Deposit'] = __('Deposit ({0})', $this->Number->currency($price->minimum_deposit));
			if ($price->deposit_only) {
				// The only option is a fixed-price deposit, so there will be no input fields at all,
				// but we want to let them know that the amount will be different than they might expect
				echo $this->Html->para('warning-message', __('This option requires a {0} deposit, with the balance to be paid off-line.', $this->Number->currency($price->minimum_deposit)));
			}
		} else {
			$options['Deposit'] = __('Deposit (minimum {0})', $this->Number->currency($price->minimum_deposit));
		}
	}
	if (!$price->deposit_only) {
		$options['Full'] = __('Full ({0})', $this->Number->currency($price->total));
	}

	if (!empty($registration)) {
		$default_type = ($registration->deposit_amount > 0 ? 'Deposit' : 'Full');
	} else {
		$default_type = 'Full';
	}
	echo $this->Jquery->toggleInput('payment_type', [
		'options' => $options,
		'hide_single' => true,
		'default' => $default_type,
		'secure' => false,
	], [
		'values' => [
			'Full' => '.full',
			'Deposit' => '.deposit',
		],
		'parent_selector' => '.form-group',
	]);

	if ($price->allow_deposit && !$price->fixed_deposit) {
		echo $this->Form->input('deposit_amount', [
			'default' => $default_type == 'Full' ? $price->minimum_deposit : $registration->deposit_amount,
			'class' => 'deposit',
			'secure' => false,
		]);
	}

	if (empty($for_edit) && $price->allow_reservations) {
		echo $this->Html->para('warning-message', __('After clicking "Submit", your registration will be reserved for you for {0}. During this time, your spot is guaranteed. After this time, if you have not yet paid, it will revert to "unpaid" status, meaning that someone else can take it.', PricesTable::duration($price->reservation_duration)));
	}

	echo $this->Html->scriptBlock("zjQuery(':button[type=\"submit\"]').prop('disabled', false);", ['buffer' => true]);
} else {
	// This happens when a price has not yet been selected, or when a previously selected price is no longer valid.
	echo $this->Html->scriptBlock("zjQuery(':button[type=\"submit\"]').prop('disabled', true);", ['buffer' => true]);
}

// We always need to unlock these inputs so that the form security component is happy.
$this->Form->unlockField('payment_type');
$this->Form->unlockField('deposit_amount');
