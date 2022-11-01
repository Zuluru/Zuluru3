<?php
/**
 * @type $team \App\Model\Entity\Team
 * @type $person \App\Model\Entity\Person
 * @type $attendance \App\Model\Entity\Attendance
 * @type $event \App\Model\Entity\TeamEvent
 */

$args = [
	'team' => $team,
	'person_id' => $person->id,
	'role' => $person->teams[0]->_joinData->role,
	'attendance' => $attendance,
	'event_id' => $event->id,
	'event' => $event,
];
echo $this->element('TeamEvents/attendance_change', $args);
