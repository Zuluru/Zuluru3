<?php

/**
 * Implementation of the runtime callback for the "junior" badge.
 */
namespace App\Module;

use Cake\Core\Configure;

class BadgeJunior extends Badge {

	public function applicable($person) {
		return (Configure::read('profile.birthdate') && !empty($person->birthdate) && $person->birthdate->addYears(18)->isFuture());
	}

}
