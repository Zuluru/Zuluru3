<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $relative
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
