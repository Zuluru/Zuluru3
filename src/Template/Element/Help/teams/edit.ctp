<?php
use Cake\Core\Configure;
?>

<p><?= __('The "edit team" page is used to update details of your team. Only coaches and captains have permission to edit team details.') ?></p>
<?php
if (Configure::read('feature.registration')):
?>
<p><?= __('Since this system uses the {0}, teams are created during the registration process with some default values that you might want to alter.',
	$this->Html->link(__('registration system'), ['controller' => 'Events', 'action' => 'wizard']) . ' ' .
	$this->Html->iconLink('help_16.png',
		['controller' => 'Help', 'action' => 'registration'],
		['alt' => __('Registration Help'), 'title' => __('Registration Help')])
) ?></p>
<?php
else:
?>
<p><?= __('The "create team" page is essentially identical to this page.') ?></p>
<?php
endif;

$topics = [
	'name',
];
if (Configure::read('feature.shirt_colour')) {
	$topics[] = 'shirt_colour';
}
if (Configure::read('feature.facility_preference')) {
	$topics[] = 'facility';
}
if (Configure::read('feature.region_preference')) {
	$topics[] = 'region_preference';
}
$topics[] = 'open_roster';
$topics['track_attendance'] = [
	'image' => 'attendance_32.png',
];

echo $this->element('Help/topics', [
	'section' => 'teams/edit',
	'topics' => $topics,
]);
