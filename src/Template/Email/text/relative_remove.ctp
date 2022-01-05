<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type $relative \App\Model\Entity\Person
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $relative->first_name) ?>


<?= __('{0} has removed you as a relative on the {1} web site.',
	$person->full_name,
	Configure::read('organization.name')
) ?>


<?= __('This is a notification only, there is no action required on your part.') ?>


<?= $this->element('Email/text/footer');
