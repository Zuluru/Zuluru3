<?php

/**
 * Implementation of the registration callback for the "past member" badge.
 */
namespace App\Module;

class BadgeMemberPast extends Badge {

	public function applicable($event) {
		return ($event->event_type->type == 'membership' && $event->membership_ends->isPast());
	}

}
