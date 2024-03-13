<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 */

use Cake\Core\Configure;

echo $this->element('Payments/invoices/' . Configure::read('payment.invoice_implementation'),
	['registrations' => [$registration]]
);
