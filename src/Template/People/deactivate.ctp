<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Deactivate'));
?>

<div class="people form">
	<h2><?= __('Deactivate Profile') ?></h2>
	<p><?= __('You have asked to deactivate your profile.') ?></p>
	<p><?= __('For accounting and historical reasons, we don\'t provide a way to delete accounts entirely. However, you can deactivate your account, which has much the same effect.') ?></p>
	<p><?= __('Keep in mind that the only information shown to someone not logged into the site (including search engines) is your name and skill level. All of your contact information is already private, so nobody but admins can see it at all.') ?></p>
	<p><?= __('If you deactivate your profile, your information will become completely invisible to search engines; they will get an error message should they try to access it. You will also cease to receive any newsletters that we may send out.') ?></p>
	<p><?= __('Note that you cannot register for anything or join a team unless your profile is active.') ?></p>
	<p><?= __('To proceed with the deactivation, simply click the "Deactivate" button below.') ?></p>
<?php
	echo $this->Form->create($person, ['align' => 'horizontal']);
	echo $this->Form->hidden('complete', ['value' => true]);
	echo $this->Form->button(__('Deactivate'), ['class' => 'btn-success']);
	echo $this->Form->end();
?>

</div>
