<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Upload $document
 */

use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('An administrator has updated the valid dates for your {0} document. It is now listed as being valid from {1} until {2}.',
	$document->upload_type->name,
	$this->Time->date($document->valid_from),
	$this->Time->date($document->valid_until)
) ?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
