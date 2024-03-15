<?php
/**
 * @var \App\View\AppView $this
 * @var string $offline
 */

use Cake\Core\Configure;

$offline = Configure::read('registration.offline_payment_text');
if (!empty($offline)) {
	if (Configure::read('registration.online_payments')) {
		$options = Configure::read('payment.offline_options');
		if (!empty($options)) {
			echo $this->Html->para(null, __('If you prefer to pay offline (via {0}), the online portion of your registration process is now complete, but you must do the following to make payment:', $options));
		} else {
			echo $this->Html->para(null, __('If you prefer to pay offline (via {0}), the online portion of your registration process is now complete, but you must do the following to make payment:', __('cheque, money transfer, etc.')));
		}
	} else {
		echo $this->Html->para(null, __('The online portion of your registration process is now complete, but you must do the following to make payment:'));
	}
	echo $offline;
}
