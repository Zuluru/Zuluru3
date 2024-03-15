<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Thanks,') ?>
<br /><?= Configure::read('email.admin_name') ?>
<br /><?= Configure::read('organization.name') ?></p>
