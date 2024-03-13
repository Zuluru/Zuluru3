<?php
/**
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 * @var \App\Model\Entity\TeamEvent $event
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
