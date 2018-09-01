<p><?= __('iCal is a standardized format for exchanging schedule information between applications. {0} supports iCal output in a variety of ways, but perhaps the most useful is the "Personal Feed".', ZULURU) ?></p>
<p><?= __('If you {0} to enable this, you will be able to have iCal, Google Calendar and others automatically pull your schedule, from week to week, season to season, and year to year, and keep you informed of all of your upcoming games.',
	$this->Html->link(__('edit your preferences'), ['controller' => 'People', 'action' => 'preferences'])
) ?></p>
<p><?= __('To add your personal feed to iCal, copy the link from the iCal logo at the bottom of the main {0} page. Then, go to the Calendar menu in iCal, pick Subscribe, and paste in the link.', ZULURU) ?></p>
<p><?= __('To add your personal feed to Google Calendar, just click the "Add to Google Calendar" link at the bottom of the main page.') ?></p>
