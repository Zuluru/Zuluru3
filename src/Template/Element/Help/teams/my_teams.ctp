<p><?= __('The "My Teams" section of the {0} home page provides a list of the teams you are on, limited to leagues that are either ongoing, closed recently, or will open soon.', ZULURU) ?></p>
<p><?= __('Clicking the team name will take you to the team details and roster page.') ?></p>
<p><?= __('To change your role on the team, including removing yourself from the roster, click on the role currently listed in parentheses after the team name. Note that you can typically only demote yourself through this method; promoting players to greater levels of responsibility must be done by a coach or captain.') ?></p>
<p><?= __('Along with the team name and your role, there will always be {0} "Schedule" and {1} "Standings" links.',
	$this->Html->iconImg('schedule_24.png'),
	$this->Html->iconImg('standings_24.png')
) ?></p>
<p><?= __('If attendance tracking is enabled for the team, there will be an {0} "Attendance Report" link here that will show you the a summary of attendance for the team across the entire season.',
	$this->Html->iconImg('attendance_24.png')
) ?></p>
<p><?= __('If you are a coach or captain of the team, there will be an {0} "Edit" link here that will allow you to edit the team details.',
	$this->Html->iconImg('edit_24.png')
) ?></p>
<p><?= __('If you are a coach or captain of the team, and the roster deadline has not yet passed, there will be an {0} "Add Player" link here that will allow you to add players through a variety of means.',
	$this->Html->iconImg('roster_add_24.png')
) ?></p>
