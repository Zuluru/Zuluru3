<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $registration \App\Model\Entity\Registration
 * @type $event \App\Model\Entity\Event
 * @type $refund \App\Model\Entity\Payment
 */
use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You have been issued a {0} of {1} for your registration for {2}.',
	$refund->payment_type == 'Refund' ? __('refund') : __('credit'),
	$this->Number->currency(-$refund->payment_amount),
	$event->name
) ?></p>
<?php
if ($refund->payment_type == 'Credit'):
?>
<p><?= __('This credit can be redeemed towards any future registration on the {0} site, or transferred to any other member (e.g. a relative or your captain) for them to use.',
	Configure::read('organization.name')
) ?></p>
<?php
endif;
?>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
