<?php
namespace Javelin\Policy;

use App\Authorization\ContextResource;
use App\Policy\AppPolicy;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class TeamPolicy extends AppPolicy {

	/**
	 * This policy overrides the default before function, because there are a few situations where admins
	 * don't actually have complete access, and because we allow some roster operations to happen
	 * through emailed links, usable by people who aren't logged in.
	 */
	public function before($identity, $resource, $action) {
		$this->blockAnonymous($identity);
		$this->blockLocked($identity);
	}

	public function canJoin(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		// If the team isn't in a division that's currently open, or opening soon, don't show it.
		if (!$team->division_id) {
			return false;
		}

		$division = $resource->division;
		return Configure::read('plugin.Javelin') &&
			($division->is_open || $division->open->isFuture()) &&
			!$team->use_javelin && ($identity->wasCaptainOf($team) || $identity->isManagerOf($team));
	}

	public function canLeave(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		// If the team isn't in a division that's currently open, or opening soon, don't show it.
		if (!$team->division_id) {
			return false;
		}

		$division = $resource->division;
		return Configure::read('plugin.Javelin') &&
			($division->is_open || $division->open->isFuture()) &&
			$team->use_javelin && ($identity->wasCaptainOf($team) || $identity->isManagerOf($team));
	}

}
