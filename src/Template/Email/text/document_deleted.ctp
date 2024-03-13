<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Upload $document
 * @var string $comment
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?php
if ($document->approved):
?>
<?= __('Your {0} document, valid from {1} until {2}, has been removed by an administrator.',
	$document->upload_type->name,
	$this->Time->date($document->valid_from),
	$this->Time->date($document->valid_until)
) ?>


<?php
	if ($document->valid_until->isPast()):
?>
<?= __('As the validity date has passed, this is most likely simply a housekeeping matter and can be safely ignored.') ?>


<?php
	endif;
else:
?>
<?= __('Your {0} document has been reviewed by an administrator and rejected as unsuitable for the desired purpose. Please review your upload to ensure that it is the correct document and easily legible, and try again.',
	$document->upload_type->name
) ?>


<?php
endif;

if (isset($comment)):
?>
<?= $comment ?>


<?php
endif;
?>
<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
