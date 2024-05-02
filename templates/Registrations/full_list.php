<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

$this->Breadcrumbs->add(__('Registrations'));
$this->Breadcrumbs->add($event->name);
$this->Breadcrumbs->add(__('List'));
?>

<div class="registrations index">
	<h2><?= __('Registration List') . ': ' . $event->name ?></h2>

	<div id="RegistrationList" class="zuluru_pagination">

<?= $this->element('Registrations/full_list') ?>

	</div>
</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
