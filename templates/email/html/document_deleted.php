<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload $person
 * @var \App\Model\Entity\Upload $document
 * @var string $comment
 */

use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<?php
if ($document->approved):
?>
<p><?= __('Your {0} document, valid from {1} until {2}, has been removed by an administrator.',
	$document->upload_type->name,
	$this->Time->date($document->valid_from),
	$this->Time->date($document->valid_until)
) ?></p>
<?php
	if ($document->valid_until->isPast()):
?>
<p><?= __('As the validity date has passed, this is most likely simply a housekeeping matter and can be safely ignored.') ?></p>
<?php
	endif;
else:
?>
<p><?= __('Your {0} document has been reviewed by an administrator and rejected as unsuitable for the desired purpose. Please review your upload to ensure that it is the correct document and easily legible, and try again.',
	$document->upload_type->name
) ?></p>
<?php
endif;

if (isset($comment)):
?>
<p><?= $comment ?></p>
<?php
endif;
?>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('email/html/footer');
