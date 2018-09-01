<?php
use Cake\Core\Configure;

$view_contact = Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;
?>
N:<?= "{$person['last_name']};{$person['first_name']}" ?>

FN:<?= $person['full_name'] ?>

<?php
if (Configure::read('profile.home_phone') && !empty($person['home_phone']) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person['publish_home_phone']))):
?>
TEL;HOME;VOICE:<?= $person['home_phone'] ?>
<?php
endif;
?>

<?php
if (Configure::read('profile.work_phone') && !empty($person['work_phone']) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person['publish_work_phone']))):
?>
TEL;WORK;VOICE:<?= $person['work_phone'] ?>
<?php
endif;
?>

<?php
if (Configure::read('profile.mobile_phone') && !empty($person['mobile_phone']) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person['publish_mobile_phone']))):
?>
TEL;CELL;VOICE:<?= $person['mobile_phone'] ?>
<?php
endif;
?>

<?php
if (!empty($person['email']) && ($view_contact || (Configure::read('Perm.is_logged_in') && $person['publish_email']))):
?>
EMAIL;PREF;INTERNET:<?= $person['email'] ?>

<?php
endif;
?>

<?php
if (!empty($person['alternate_email']) && ($view_contact || (Configure::read('Perm.is_logged_in') && $person['publish_alternate_email']))):
?>
EMAIL;INTERNET:<?= $person['alternate_email'] ?>

<?php
endif;
