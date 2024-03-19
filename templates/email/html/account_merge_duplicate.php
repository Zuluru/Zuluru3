<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $existing
 */

use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?php
echo __('You seem to have created a duplicate {0} account.', Configure::read('organization.short_name'));
if (!empty($existing->user_name)) {
	echo ' ' . __('You already have an account with the username {0} created using the email address {1}.',
		$existing->user_name, $existing->email
	);
} else {
	echo ' ' . __('You already have an account.');
}
?></p>
<p><?= __('To preserve historical information (registrations, team records, etc.) this old account has been merged with your new information. You will be able to access this account with your newly chosen username and password.') ?></p>
<?= $this->element('Email/html/footer');
