<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add($division->long_league_name);
$this->Breadcrumbs->add(__('Add Teams'));
?>

<div class="teams form">
	<?= $this->Form->create($division, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Team Names') ?></legend>
<?php
$colours = Configure::read('automatic_team_colours');
echo $this->Html->para(null, __('This can be used to create up to {0} teams at once. To create less, simply leave those names blank.', count($colours)));
foreach ($colours as $key => $colour) {
	$num = $key + 1;
	echo $this->Form->control("teams.$key.name", [
		'label' => "$num.",
		'required' => false,
		'help' => false,
	]);
	if (Configure::read('feature.shirt_colour')) {
		echo $this->Form->hidden("teams.$key.shirt_colour", ['value' => $colour]);
	}
}
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Team Details') ?></legend>
<?php
echo $this->Form->control('teams.0.open_roster', [
	'help' => __('If the team roster is open, others can request to join; otherwise, only a coach or captain can add players.'),
]);

echo $this->Jquery->toggleInput('teams.0.track_attendance', [
	'type' => 'checkbox',
	'help' => __('If selected, the system will help you to monitor attendance on a game-to-game basis.'),
], [
	'selector' => '#AttendanceDetails',
]);
?>

		<fieldset id="AttendanceDetails">
<?php
echo $this->Form->control('teams.0.attendance_reminder', [
	'size' => 1,
	'default' => 3,
	'help' => __('Reminder emails will be sent to players that have not finalized their attendance this many days before the game. 0 means the day of the game, -1 will disable these reminders.'),
	'secure' => false,
]);
echo $this->Form->control('teams.0.attendance_summary', [
	'size' => 1,
	'default' => 1,
	'help' => __('Attendance summary emails will be sent to coaches and captains this many days before the game. 0 means the day of the game, -1 will disable these summaries.'),
	'secure' => false,
]);
echo $this->Form->control('teams.0.attendance_notification', [
	'size' => 1,
	'default' => 1,
	'help' => __('Emails notifying coaches and captains about changes in attendance status will be sent starting this many days before the game. 0 means the day of the game, -1 will disable these notifications. You will never receive notifications about any changes that happen before this time.'),
	'secure' => false,
]);
?>
		</fieldset>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
