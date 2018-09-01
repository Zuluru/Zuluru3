<?php
use Cake\Core\Configure;
?>

<p>Thanks,
<br /><?= Configure::read('email.admin_name') ?>
<br /><?= Configure::read('organization.name') ?></p>
