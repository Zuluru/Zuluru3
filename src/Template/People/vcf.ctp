<?php
/**
 * @type \App\Model\Entity\Person $person
 */

$visible_properties = $person->getVisible();
?>
N:<?= "{$person->last_name};{$person->first_name}" ?>

FN:<?= $person->full_name ?>

<?php
if (in_array('home_phone', $visible_properties) && !empty($person->home_phone)):
?>
TEL;HOME;VOICE:<?= $person->home_phone ?>
<?php
endif;
?>

<?php
if (in_array('work_phone', $visible_properties) && !empty($person->work_phone)):
?>
TEL;WORK;VOICE:<?= $person->work_phone ?>
<?php
	if (!empty($person->work_ext)) {
		echo ";ext={$person->work_ext}";
	}
endif;
?>

<?php
if (in_array('mobile_phone', $visible_properties) && !empty($person->mobile_phone)):
?>
TEL;CELL;VOICE:<?= $person->mobile_phone ?>
<?php
endif;
?>

<?php
if (in_array('email', $visible_properties) && !empty($person->email)):
?>
EMAIL;PREF;INTERNET:<?= $person->email ?>

<?php
endif;
?>

<?php
if (in_array('alternate_email', $visible_properties) && !empty($person->alternate_email)):
?>
EMAIL;INTERNET:<?= $person->alternate_email ?>

<?php
endif;
