<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type $relative \App\Model\Entity\Person
 */

use Cake\Core\Configure;
use Cake\Utility\Text;
?>

<p><?= __('Dear {0},', Text::toList([$person->first_name, $relative->first_name])) ?></p>
<p><?= __('An administrator has removed the relation between you on the {0} web site.',
	Configure::read('organization.name')
) ?></p>
<p><?= __('If you believe that this happened in error, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
