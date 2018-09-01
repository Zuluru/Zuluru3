<?php

/**
 * Implementation of the registration callback for the "full member" badge.
 */
namespace App\Module;

use Cake\Core\Configure;

class BadgeMemberRegistered extends Badge {

	public function applicable($event) {
		return ($event->event_type->type == 'membership' &&
			Configure::read("membership_types.badge.{$event->membership_type}") == 'member_registered' &&
			!$event->membership_begins->isFuture() && !$event->membership_ends->isPast());
	}

}
