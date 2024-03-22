<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 * @var \App\Model\Entity\Person $person
 * @var string $comment
 */

use Cake\Core\Configure;
?>
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('Your {0} badge has been removed.', $badge->name) ?></p>
<?php
if (!empty($comment)):
?>
<p><?= __('The administrator provided this comment:') ?></p>
<p><?= $comment ?></p>
<?php
endif;
?>
<p><?= __('If you believe that this happened in error, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('email/html/footer');
