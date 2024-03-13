<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Registration History'));
?>

<div class="people registrations">
	<h2><?= __('Registration History') . ': ' . $person->full_name ?></h2>

	<div id="RegistrationList" class="zuluru_pagination">

		<?= $this->element('People/registrations') ?>

	</div>
</div>
