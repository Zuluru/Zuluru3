<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>
<h3><?= __('Login') ?></h3>
<?= $this->element('Users/login_notice') ?>
<?php
if ($failed):
?>
<p><?= __('{0} If you already have an account from a previous season, {1}! Instead, please {2} to regain access to your account.', [
	$this->Html->tag('strong', __('NOTE') . ': '),
	$this->Html->tag('strong', __('DO NOT CREATE ANOTHER ONE')),
	$this->Html->link(__('follow these instructions'), Configure::read('App.urls.resetPassword'))
]) ?></p>
<?php
endif;

echo $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => $redirect]]]);
echo $this->Form->control("$user_field", [
	'label' => false,
	'id' => 'UserName',
	'placeholder' => __('Username'),
	'tabindex' => 1,
	'help' => $this->Html->link(__('I forgot my username'), ['action' => 'reset_password']),
]);
echo $this->Form->control("$pwd_field", [
	'type' => 'password',
	'label' => false,
	'placeholder' => __('Password'),
	'tabindex' => 1,
	'help' => $this->Html->link(__('I forgot my password'), ['action' => 'reset_password']),
]);
echo $this->Form->control('remember_me', [
	'type' => 'checkbox',
	'tabindex' => 1,
]);

echo $this->Form->button(__('Login'), ['class' => 'btn-success', 'tabindex' => 1]);
echo $this->Form->end();

// TODO: Add a jQuery initialization that sets focus to something with a class? This is currently the only place we do this.
$this->Html->scriptBlock('zjQuery("#UserName").focus();', ['buffer' => true]);
?>
