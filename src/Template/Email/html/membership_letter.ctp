<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
?>

<?php
$year = max($year, FrozenTime::now()->year);
$year_end = $event->membership_ends->year;
if ($year_end != $year) {
	$year = "$year/$year_end";
}
?>
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('Welcome to {0}!', Configure::read('organization.short_name')) ?></p>
<p><?= __('If you\'re renewing a past membership, we welcome you back. If you\'re new to the Club, we welcome you in! Being a member of our Club is a unique experience and one that we hope that you will enjoy for years to come.') ?></p>
<p><?= __('Your membership runs from {0} to {1}.',
	$this->Time->date($event->membership_begins),
	$this->Time->date($event->membership_ends)
) ?></p>
<p><?= __('If you have any questions regarding your membership, league concerns, or otherwise please feel free to contact us at {0}.',
	$this->Html->link(Configure::read('email.admin_email'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<p><?= __('Have a great season in {0}!', $year) ?></p>
<?= $this->element('Email/html/footer');
