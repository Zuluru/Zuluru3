<?php
echo $this->element('Help/topics', [
	'section' => 'games',
	'topics' => [
		'recent_and_upcoming' => 'Recent and Upcoming Games',
		'personal_feed',
		'reminder_emails',
		'date_time_format' => 'Date/Day/Time Format',
	],
]);
