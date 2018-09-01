<?php
use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $relative->first_name) ?></p>
<p><?= __('Your relative request to {0} on the {1} web site has been approved.',
	$person->full_name,
	Configure::read('organization.name')
) ?></p>
<?= $this->element('Email/html/footer');
