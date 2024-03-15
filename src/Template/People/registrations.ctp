<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add($person->full_name);
$this->Breadcrumbs->add(__('Registration History'));
?>

<div class="people registrations">
	<h2><?= __('Registration History') . ': ' . $person->full_name ?></h2>

	<div id="RegistrationList" class="zuluru_pagination">

		<?= $this->element('People/registrations') ?>

	</div>
</div>
