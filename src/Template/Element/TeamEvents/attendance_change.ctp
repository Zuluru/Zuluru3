<?php
/**
 * @type $team \App\Model\Entity\Team
 * @type $attendance \App\Model\Entity\Attendance
 * @type $event \App\Model\Entity\TeamEvent
 * @type $role string
 * @type $person_id int
 * @type $future_only bool
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Utility\Text;
use App\Model\Table\GamesTable;

if ($team->track_attendance) {
	$status = $attendance ? $attendance->status : ATTENDANCE_UNKNOWN;
	$comment = $attendance ? $attendance->comment : null;
	$long = Configure::read("attendance.$status");
	$icon = Text::slug(strtolower($long), '_');

	if (isset($dedicated) && $dedicated) {
		$icon .= '_dedicated';
	} else {
		$dedicated = false;
	}

	$title = __('Current attendance: {0}', __($long));
	if (!empty($comment)) {
		if ($dedicated) {
			$icon .= '_comment';
		}
		$title .= " ($comment)";
	}

	$short = $this->Html->iconImg("attendance_{$icon}_24.png", [
		'title' => $title,
		'alt' => Configure::read("attendance_alt.$status"),
	]);

	$context = new ContextResource($team, [
		'attendance' => $attendance,
		'event' => isset($event) ? $event : null,
		'future_only' => isset($future_only) ? $future_only : false,
	]);
	if ($this->Authorize->can('attendance_change', $context)) {
		$identity = $this->Authorize->getIdentity();

		$url = ['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id];
		if (!$identity->isMe($person_id)) {
			$url['person'] = $person_id;
		}

		$valid_options = array_keys(GamesTable::attendanceOptions($role, $status, !$context->future, in_array($team->id, $this->UserCache->read('OwnedTeamIDs'))));
		if ($context->future) {
			$valid_options[] = 'comment';
		}

		echo $this->Jquery->inPlaceWidget($short, [
			'type' => 'attendance',
			'url' => $url,
			'valid-options' => $valid_options,
			'comment-value' => $comment,
		], [
			'class' => "attendance_status_$status " .  strtolower(Configure::read("attendance.$status")),
		], false, compact('dedicated'));
	} else if (empty($future_only)) {
		echo $this->Html->tag('span', $short, ['class' => "attendance_status_$status " .  strtolower(Configure::read("attendance.$status"))]);
	}
}
