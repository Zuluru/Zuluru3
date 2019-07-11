<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
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
<p><?= __('Your second account has been deleted. If you cannot remember your password for the existing account, please use the "{0}" feature and a new password will be emailed to you.',
	$this->Html->link(__('Forgot your password?'), Router::url(Configure::read('App.urls.resetPassword'), true))
) ?></p>
<p><?= __('If the above email address is no longer correct, please reply to this message and request an address change.') ?></p>
<?= $this->element('Email/html/footer');
