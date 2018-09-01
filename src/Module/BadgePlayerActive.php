<?php

/**
 * Implementation of the team callback for the "active player" badge.
 */
namespace App\Module;

class BadgePlayerActive extends Badge {

	public function applicable($team) {
		return ($team->division_id && !empty($team->division->is_open));
	}

}
