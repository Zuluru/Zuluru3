<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $relative
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your relative request to {0} on the {1} web site has been approved.',
	$relative->full_name,
	Configure::read('organization.name')
) ?>


<?= $this->element('Email/text/footer');
