<?php
// This is required on every page where the attendance change popup is used
use Cake\Core\Configure;

$options = Configure::read('attendance');

// When inviting subs, provide an option to add a comment
$options[ATTENDANCE_INVITED] = [
	'dialog' => 'attendance_comment_to_player_div',
	'text' => $options[ATTENDANCE_INVITED],
];
$options['comment'] = [
	'dialog' => 'attendance_comment_to_captain_div',
	'text' => __('Comment'),
];
echo $this->Jquery->inPlaceWidgetOptions($options, [
	'type' => 'attendance',
	'url-param' => 'status',
	'ajax' => true,
]);
?>
<div id="attendance_comment_to_player_div" style="display: none;" title="<?= __('Attendance comment') ?>"><form>
	<p><?= __('If you want to add a personal note to the player, do so here. To include no note with this invitation, leave this blank, but click "Save". "Cancel" will abort the invitation entirely.') ?></p>
	<br /><?= $this->Form->input('note', [
		'label' => false,
		'size' => 50,
	]) ?>
</form></div>
<div id="attendance_comment_to_captain_div" style="display: none;" title="<?= __('Attendance comment') ?>"><form>
	<p><?= __('If you want to add a comment for your coaches or captains, do so here.') ?></p>
	<br /><?= $this->Form->input('comment', [
		'label' => false,
		'size' => 50,
	]) ?>
</form></div>
