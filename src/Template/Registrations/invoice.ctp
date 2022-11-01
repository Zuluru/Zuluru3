<?php
/**
 * @type $this \App\View\AppView
 * @type $registration \App\Model\Entity\Registration
 */

use Cake\Core\Configure;

echo $this->element('Payments/invoices/' . Configure::read('payment.invoice_implementation'),
	['registrations' => [$registration]]
);
