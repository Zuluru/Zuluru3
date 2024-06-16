<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Credit $credit
 */

use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You have been issued a credit of {0} ({1}).',
	$this->Number->currency($credit->amount),
	$credit->notes
) ?></p>
<p><?= __('This credit can be redeemed towards any future registration on the {0} site, or transferred to any other member (e.g. a relative or your captain) for them to use.',
	Configure::read('organization.name')
) ?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('email/html/footer');