<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $nominator
 */

?>
<p><?= __('Dear {0},', $nominator->first_name) ?></p>
<p><?= __('Your nomination of {0} for the {1} badge has been approved and is now visible to other members who are logged in to this site.',
	$person->full_name,
	$badge->name
) ?></p>
<?= $this->element('email/html/footer');
