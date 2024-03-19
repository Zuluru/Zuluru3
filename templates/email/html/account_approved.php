<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('Your {0} account has been approved.', Configure::read('organization.short_name')) ?></p>
<?php
if (!empty($person->user_name)):
?>
<p><?= __('You may now log in to the system with the username and the password you specified when you created your account.', $person->user_name) ?></p>
<?php
endif;
?>
<?= $this->element('Email/html/footer');
