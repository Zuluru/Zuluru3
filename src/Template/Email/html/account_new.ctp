<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('A user account with this e-mail address has been created for you on the {0} web site ({1}).',
	Configure::read('organization.name'),
	Router::url('/', true)
) ?></p>
<p><?= __('Your new username is: {0}', $user->$user_model->$user_field) ?></p>
<p><?= __('Your new password is: {0}', $user->$user_model->new_password) ?></p>
<p><?= __('After you login, you can change your username and other profile details {0} and change your password {1}.',
	$this->Html->link(__('here'), Router::url(['controller' => 'People', 'action' => 'edit'], true)),
	$this->Html->link(__('here'), Router::url(['controller' => 'Users', 'action' => 'change_password'], true))
) ?></p>
<?= $this->element('Email/html/footer');
