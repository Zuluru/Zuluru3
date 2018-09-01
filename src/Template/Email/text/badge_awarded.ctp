<?= __('Dear {0},', $person->first_name) ?>


<?php
echo __('You have been awarded the {0} badge.', $badge->name) . ' ' . $badge->description;
if ($badge->category == 'nominated') {
	echo ' ' . __('You were nominated for this by {1}.', $nominator->full_name);
}
echo ' ' . __('This badge is now visible to other members who are logged in to this site.');
?>


<?php
if (!empty($link->reason)):
	if ($badge->category == 'nominated') {
		echo __('When they nominated you, {0} said:', $nominator->first_name);
	} else {
		echo __('When they assigned this badge, the administrator provided this reason:');
	}
?>


<?= $link->reason; ?>


<?php
endif;
?>
<?= $this->element('Email/text/footer');
