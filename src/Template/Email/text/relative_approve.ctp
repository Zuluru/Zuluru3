<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type $relative \App\Model\Entity\Person
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your relative request to {0} on the {1} web site has been approved.',
	$relative->full_name,
	Configure::read('organization.name')
) ?>


<?= $this->element('Email/text/footer');
