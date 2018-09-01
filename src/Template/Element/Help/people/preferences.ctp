<p><?= __('On the {0} page, you can set a number of options which change the way the site works for you.',
	$this->Html->link(__('My Profile') . ' -> ' . __('Preferences'), ['controller' => 'People', 'action' => 'preferences'])
) ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'games',
	'topics' => [
		'personal_feed' => 'Enable Personal iCal Feed',
		'reminder_emails' => 'Always Send Attendance Reminder Emails',
		'date_time_format' => 'Date/Day/Time Format',
	],
	'compact' => true,
]);
