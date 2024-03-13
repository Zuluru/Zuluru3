<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Reactivate'));
?>

<div class="people form">
	<h2><?= __('Reactivate Profile') ?></h2>
	<p><?= __('You have asked to reactivate your profile.') ?></p>
	<p><?= __('If you reactivate your profile, your basic information (name and skill level) will become visible to search engines again. You may also receive any newsletters that we may send out.') ?></p>
	<p><?= __('Note that you cannot register for anything or join a team unless your profile is active.') ?></p>
	<p><?= __('To proceed with the reactivation, simply click the "Reactivate" button below.') ?></p>
<?php
	echo $this->Form->create($person, ['align' => 'horizontal']);
	echo $this->Form->hidden('complete', ['value' => true]);
	echo $this->Form->button(__('Reactivate'), ['class' => 'btn-success']);
	echo $this->Form->end();
?>

</div>
