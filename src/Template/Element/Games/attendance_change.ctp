<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\Attendance $attendance
 * @type \App\Model\Entity\Game $game
 * @type \Cake\I18n\Date $game_date
 * @type string $role
 * @type int $person_id
 * @type bool $future_only
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Utility\Text;
use App\Model\Table\GamesTable;

if ($team->track_attendance || (isset($force) && $force)) {
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

	$context = new ContextResource($team, compact('attendance', 'game', 'game_date', 'future_only'));
	if ($this->Authorize->can('attendance_change', $context)) {
		$identity = $this->Authorize->getIdentity();

		$url = ['controller' => 'Games', 'action' => 'attendance_change', 'team' => $team->id];
		if (isset($game_date)) {
			$url['date'] = $game_date->format('Y-m-d');
		} else if ($game->id) {
			$url['game'] = $game->id;
		} else {
			$url['date'] = $game->game_slot->game_date->format('Y-m-d');
		}

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
