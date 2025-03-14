<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Payment $refund
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You have been issued a {0} of {1} for your registration for {2}.',
	$refund->payment_type == 'Refund' ? __('refund') : __('credit'),
	$this->Number->currency(-$refund->payment_amount),
	$event->name
) ?>


<?php
if ($refund->payment_type == 'Credit'):
?>
<?= __('This credit can be redeemed towards any future registration on the {0} site, or transferred to any other member (e.g. a relative or your captain) for them to use.',
	Configure::read('organization.name')
) ?>


<?php
endif;
?>
<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('email/text/footer');
