<?php
use Cake\Core\Configure;
?>

<?= __('Dear {0},', $relative->first_name) ?>


<?= __('Your relative request to {0} on the {1} web site has been approved.',
	$person->full_name,
	Configure::read('organization.name')
) ?>


<?= $this->element('Email/text/footer');
