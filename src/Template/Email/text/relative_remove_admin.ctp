<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type $relative \App\Model\Entity\Person
 */

use Cake\Core\Configure;
use Cake\Utility\Text;
?>

<?= __('Dear {0},', Text::toList([$person->first_name, $relative->first_name])) ?>


<?= __('An administrator has removed the relation between you on the {0} web site.',
	Configure::read('organization.name')
) ?>


<?= __('If you believe that this happened in error, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
