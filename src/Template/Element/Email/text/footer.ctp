<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<?= __('Thanks,') ?>

<?= Configure::read('email.admin_name') ?>

<?= Configure::read('organization.name');
