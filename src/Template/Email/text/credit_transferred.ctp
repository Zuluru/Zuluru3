<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $credit \App\Model\Entity\Credit
 */
use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('A credit with a balance of {0} has been transferred to you.',
	$this->Number->currency($credit->balance)
) ?>


<?= __('This credit can be redeemed towards any future purchase on the {0} site, or transferred to any other member (e.g. a relative or your captain) for them to use.',
	Configure::read('organization.name')
) ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
