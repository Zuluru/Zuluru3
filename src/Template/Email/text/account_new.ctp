<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('A user account with this e-mail address has been created for you on the {0} web site ({1}).',
	Configure::read('organization.name'),
	Router::url('/', true)
) ?>


<?= __('Your new username is: {0}', $user->$user_model->$user_field) ?>


<?= __('Your new password is: {0}', $user->$user_model->new_password) ?>


<?= __('After you login, you can change your username and other profile details at') ?>

<?= Router::url(['controller' => 'People', 'action' => 'edit'], true) ?>

<?= __('and change your password at') ?>

<?= Router::url(['controller' => 'Users', 'action' => 'change_password'], true) ?>


<?= $this->element('Email/text/footer');
