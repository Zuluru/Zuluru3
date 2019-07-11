<p><?= __('The "{0}" page is used to update details of your division. Only coordinators have permission to edit division details.',
	__('Edit Division')
) ?></p>
<p><?= __('Divisions are initially set up and configured by a system administrator, so coordinators should only make changes to these settings in extreme circumstances, and they should inform the administrator of any such changes. However, it\'s useful to understand the meanings of the various settings for your division.') ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p><?= __('The "{0}" page has division sections which are essentially identical to this page.',
	__('Edit League')
) ?></p>
<?php
endif;

echo $this->element('Help/topics', [
	'section' => 'divisions/edit',
	'topics' => [
		'name',
		'schedule_type',
		'current_round',
		'games_before_repeat',
		'exclude_teams',
		'double_booking',
		'rating_calculator',
	],
	'compact' => true,
]);
