<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Users'));
$this->Html->addCrumb($user->person->full_name);
$this->Html->addCrumb(__('Change Password'));
?>

<div class="users form">
<?= $this->Form->create($user, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Change Password for {0}', $user->person->full_name) ?></legend>
<?php
$identity = $this->Authorize->getIdentity();
// Admins must still enter their own passwords, just not for others.
if ($identity->isMe($user) || !$identity->isManagerOf($user)) {
	echo $this->Form->input('old_password', ['type' => 'password', 'label' => __('Existing Password'), 'value' => '']);
}
echo $this->Form->input('new_password', ['type' => 'password', 'label' => __('New Password')]);
echo $this->Form->input('confirm_password', ['type' => 'password', 'label' => __('Confirm Password')]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
