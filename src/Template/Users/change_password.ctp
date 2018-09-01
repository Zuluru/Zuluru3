<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Users'));
$this->Html->addCrumb($user->person->full_name);
$this->Html->addCrumb(__('Change Password'));
?>

<div class="users form">
<?= $this->Form->create($user, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Change Password for') . ' ' . $user->person->full_name ?></legend>
<?php
if (!Configure::read('Perm.is_admin') || $is_me) {
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
