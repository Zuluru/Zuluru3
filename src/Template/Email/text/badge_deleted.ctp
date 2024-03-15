<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 * @var \App\Model\Entity\Person $person
 * @var string $comment
 */

use Cake\Core\Configure;
?>
<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your {0} badge has been removed.', $badge->name) ?>


<?php
if (!empty($comment)):
?>
<?= __('The administrator provided this comment:') ?>


<?= $comment ?>


<?php
endif;
?>
<?= __('If you believe that this happened in error, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
