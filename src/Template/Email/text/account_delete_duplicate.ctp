<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?php
echo __('You seem to have created a duplicate {0} account.', Configure::read('organization.short_name'));
if (!empty($existing->user_name)) {
	echo ' ' . __('You already have an account with the username {0} created using the email address {1}.',
		$existing->user_name, $existing->email
	);
} else {
	echo ' ' . __('You already have an account.');
}
?>


<?= __('Your second account has been deleted. If you cannot remember your password for the existing account, please use the \'Forgot your password?\' link below and a new password will be emailed to you.') ?>

<?= Router::url(Configure::read('App.urls.resetPassword'), true) ?>


<?= __('If the above email address is no longer correct, please reply to this message and request an address change.') ?>


<?= $this->element('Email/text/footer');
