<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Attendance $attendance
 * @type \App\Model\Entity\TeamEvent $event
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
