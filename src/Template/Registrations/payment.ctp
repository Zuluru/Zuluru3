<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Html->addCrumb(__('Online Transaction Result'));
?>

<?php
if ($result === true) {
	echo $this->element('Payments/invoices/' . Configure::read('payment.invoice_implementation'));
	foreach ($errors as $error) {
		echo $this->Html->para('error-message', $error);
	}
} else {
	echo $this->Html->para('error-message', 'Your payment was declined. The reason given was:');
	echo $this->Html->para('error-message', $audit['message']);
	echo $this->element('Payments/offline');
	echo $this->Html->para(null, 'Alternately, you can ' .
		$this->Html->link('return to the checkout page', Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true), ['onclick' => 'close_and_redirect("' . Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true) . '")']) .
		' and try a different payment option.');
}

if (Configure::read('payment.popup')) {
	echo $this->Html->para(null, __('Click {0} to close this window.',
		$this->Html->link(__('here'), Router::url('/', true), ['onclick' => 'close_and_redirect("' . Router::url('/', true) . '")'])
	));
	$this->Html->scriptBlock('
function close_and_redirect(url) {
	window.opener.location.href = url;
	window.close();
}
	', ['block' => true, 'buffer' => true]);
}
