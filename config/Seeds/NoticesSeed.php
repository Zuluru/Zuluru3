<?php
use Migrations\AbstractSeed;

/**
 * Notices seed.
 */
class NoticesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'id' => '1',
				'sort' => '1',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Zuluru has system notices!</h2><p>This is the first in a series of notices telling you about new or lesser-known features of the system, to help you get the most out of the web site.</p><p>To dismiss this notice and not see it again, click "Okay, got it" below. To have it show you this notice again some time in the future, click "I\'m busy, remind me later".</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '2',
				'sort' => '5',
				'display_to' => 'captain',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Attendance tracking!</h2><p>Zuluru can do attendance tracking! Edit your team (look for the <%icon edit_24.png %> icon) to turn this popular feature on. You can also customize which emails will be sent to you and your players, and when.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '3',
				'sort' => '6',
				'display_to' => 'coordinator',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Playoff scheduling!</h2><p>Zuluru can do playoff scheduling! When you start the "add games" process, below the selection for what type of games to add, you will have a link to create the playoff schedule. This can be done <strong>at any time</strong>. The schedule that it creates will have placeholders for various seeds, as well as winners and losers of previous games.</p><p>When the regular season is done and all game results are in, go to the league schedule page, and click the <%icon initialize_24.png %> icon to replace the various seeds with actual teams. After that, the various winner and loser slots will be filled in automatically as the playoff results come in!</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '4',
				'sort' => '7',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Set your attendance in advance!</h2><p>If your team is using the attendance tracking feature, you can set your attendance in advance. You might use this to indicate when you\'ll be on vacation, for example, giving your captain plenty of notice.</p><p>Go to the <%link all splash "Dashboard" %> page and look for the <%icon attendance_24.png %> icon next to your team name in the "My Teams" section.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '5',
				'sort' => '8',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Attendance reminder emails</h2><p>If your team is using the attendance tracking feature and you\'ve set your attendance in advance, you can still get email notifications with game details.</p><p>Go to <%link people preferences My Profile -> Preferences %> and turn on the "Always Send Attendance Reminder Emails" option.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '6',
				'sort' => '9',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Sync your <%setting organization.short_name %> schedule to your phone or calendar!</h2><p>Zuluru can export your personal perpetual schedule to anything that supports the iCal protocol, including iCal, Google Calendar, Thunderbird and more. Rather than adding each team individually as the seasons start, add this once and it will find every game you ever have!</p><p>Go to <%link people preferences My Profile -> Preferences %> to enable this option. After you do this, there will be a link at the bottom of the <%link all splash "Dashboard" %> page with online help about how to make it work with your software.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '7',
				'sort' => '10',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Dates and times</h2><p>You can change your preferences for how dates and times are displayed in Zuluru. Don\'t like 24 hour military-style times? Change it in <%link people preferences My Profile -> Preferences %>!</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '8',
				'sort' => '11',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Online help</h2><p>Zuluru has online help. Click the <%icon help_24.png %> icon anywhere to get a popup window with help about that item.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '9',
				'sort' => '12',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Pop-up details</h2><p>When you hover your mouse over player, team, field or league names, popups appear with handy links to the most commonly requested pages, saving you valuable clicks.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '10',
				'sort' => '13',
				'display_to' => 'captain',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Ever submit the wrong score?</h2><p>If you submit an incorrect score for a game, you can correct it with the "Edit Score" link.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '11',
				'sort' => '14',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Player photos</h2><p>You can upload a photo of yourself! Captains often use photos to figure out who they want to nominate as an all-star or draft or recruit for their team. Your photo will only be visible to people who are logged in to the site.</p><p>Go to <%link people photo_upload My Profile -> Upload Photo %> to get started.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '13',
				'sort' => '15',
				'display_to' => 'player',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Your feedback is important!</h2><p>There are many ways that you can help to improve Zuluru.<ul><li>Send feature requests to <a href="mailto:admin@zuluru.org">admin@zuluru.org</a>. Many current Zuluru features are the result of user requests.</li><li>Use the "Report a bug" link at the bottom of every page to report bugs.</li><li>Use the link at the bottom of any help page to send suggestions for improvements to that help text.</li></ul></p><p>Your participation makes Zuluru better for you and everyone else.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '14',
				'sort' => '2',
				'display_to' => 'player',
				'repeat_on' => 'annual',
				'notice' => __d('seeds', '<h2>Double-check your details</h2><p>Please take a moment to confirm your address details below. <%setting organization.short_name %> uses this information for insurance and field acquisition purposes, so it\'s important that it is correct. To make any changes, go to <%link people edit My Profile -> Edit %>. If everything is correct, there is no action required.</p><p><%person first_name %> <%person last_name %><br /><%person addr_street %><br /><%person addr_city %>, <%person addr_prov %>, <%person addr_country %><br /><%person addr_postalcode %>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '15',
				'sort' => '3',
				'display_to' => 'player',
				'repeat_on' => 'annual',
				'notice' => __d('seeds', '<h2>Double-check your details</h2><p>Please take a moment to confirm your contact information below. <%setting organization.short_name %> and your captains use this information to communicate with you, so it\'s important that it is correct. To make any changes, go to <%link people edit My Profile -> Edit %>. If everything is correct, there is no action required.</p><p><%person first_name %> <%person last_name %><br />Email: <%person email %><br />Alternate Email: <%person alternate_email %><br />Home phone: <%person home_phone %><br />Work phone: <%person work_phone %> <%person work_ext %><br />Mobile phone: <%person mobile_phone %>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
			[
				'id' => '16',
				'sort' => '4',
				'display_to' => 'translation',
				'repeat_on' => null,
				'notice' => __d('seeds', '<h2>Zuluru is now available in French!</h2><p>We know the translations aren’t great. To help fix that, <a href="https://zuluru.org/translation" target="_blank">join the translation team</a>. It takes just two minutes to get started.</p>'),
				'active' => '1',
				'effective_date' => '2011-10-27 00:00:00',
				'created' => '2011-10-27 00:00:00',
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('notices');
		$table->insert($this->data())->save();
	}
}
