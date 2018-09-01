<?php
use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your {0} document has been approved by an administrator as being valid from {1} until {2}.',
	$document->upload_type->name,
	$this->Time->date($document->valid_from),
	$this->Time->date($document->valid_until)
) ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
