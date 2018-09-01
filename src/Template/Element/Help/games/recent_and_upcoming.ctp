<?php
use Cake\Core\Configure;
?>

<p><?= __('The "Recent and Upcoming Schedule" section of the {0} home page provides a timely snapshot of the most important events on your schedule.', ZULURU) ?></p>
<p><?= __('Recently played games will be listed, with results where known. If you are a coach or captain, you will be able to enter scores for games here, with the "Submit" link. If you\'ve entered an incorrect score, you can change it with the "Edit score" button, until the game is finalized.') ?></p>
<p><?= __('Upcoming games will also be listed. Opponent names and shirt colours are shown for quick reference, and you can click on team names to see their details and roster. A location code is also given; hovering over that will show the full name, and clicking on it will take you to a page with full details, including a further link to a Google map.') ?></p>
<p><?= __('Also shown will be recent and upcoming team events (if there are any){0}.',
	(Configure::read('feature.tasks') ? __(', and any tasks assigned to you') : '')
) ?>.</p>
<p><?= __('All upcoming items will have an iCal link next to them, which you can use to import the details into any software that works with the iCal format (including Outlook, iCal, Google Calendar and others).') ?></p>
