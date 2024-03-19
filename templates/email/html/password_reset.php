<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $code
 */
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('The user account {0} at {1} has this e-mail address associated with it.',
	$user->user_name,
	Configure::read('organization.name')
) ?></p>
<p><?= __('Someone has just requested a confirmation code to change your password.') ?></p>
<p><?= __('Click {0} to confirm this request, and a new password will be created and emailed to you.',
	$this->Html->link(__('here'), Router::url(['controller' => 'Users', 'action' => 'reset_password', $user->id, $code], true))
) ?></p>
<p><?= __('If you didn\'t ask for this, don\'t worry. Just delete this e-mail message and your password will remain unchanged.') ?></p>
<?= $this->element('Email/html/footer');
