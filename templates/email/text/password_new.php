<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $password
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('The user account {0} at {1} has this e-mail address associated with it.',
	$user->user_name,
	Configure::read('organization.name')
) ?>


<?= __('Someone has just requested a new password.') ?>


<?= __('Your new password is: {0}', $password) ?>


<?= __('After you login, you can change it at {0}',
	Router::url(['controller' => 'Users', 'action' => 'change_password'], true)
) ?>


<?= __('If you didn\'t ask for this, don\'t worry. You are seeing this message, not \'them\'. If this was an error just log in with your new password.') ?>


<?= $this->element('email/text/footer');
