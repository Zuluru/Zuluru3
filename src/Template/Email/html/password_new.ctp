<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $password
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('The user account {0} at {1} has this e-mail address associated with it.',
	$user->user_name,
	Configure::read('organization.name')
) ?></p>
<p><?= __('Someone has just requested a new password.') ?></p>
<p><?= __('Your new password is: {0}', $password) ?></p>
<p><?= __('After you login, you can change it {0}.',
	$this->Html->link(__('here'), Router::url(['controller' => 'Users', 'action' => 'change_password'], true))
) ?></p>
<p><?= __('If you didn\'t ask for this, don\'t worry. You are seeing this message, not \'them\'. If this was an error just log in with your new password.') ?></p>
<?= $this->element('Email/html/footer');
