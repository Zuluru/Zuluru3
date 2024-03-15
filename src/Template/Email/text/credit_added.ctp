<?php
/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Credit $credit
 */
use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You have been issued a credit of {0} ({1}).',
	$this->Number->currency($credit->amount),
	$credit->notes
) ?>


<?= __('This credit can be redeemed towards any future registration on the {0} site, or transferred to any other member (e.g. a relative or your captain) for them to use.',
	Configure::read('organization.name')
) ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
