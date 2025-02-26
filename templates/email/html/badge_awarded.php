<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 * @var \App\Model\Entity\BadgesPerson $link
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $nominator
 */
?>
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?php
echo __('You have been awarded the {0} badge.', $badge->name) . ' ' . $badge->description;
if ($badge->category == 'nominated') {
	echo ' ' . __('You were nominated for this by {1}.', $nominator->full_name);
}
echo ' ' . __('This badge is now visible to other members who are logged in to this site.');
?></p>
<?php
if (!empty($link->reason)):
?>
<p><?php
	if ($badge->category == 'nominated') {
		echo __('When they nominated you, {0} said:', $nominator->first_name);
	} else {
		echo __('When they assigned this badge, the administrator provided this reason:');
	}
?></p>
<p><?= $link->reason; ?></p>
<?php
endif;
?>
<?= $this->element('email/html/footer');
