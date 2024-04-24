<?php
declare(strict_types=1);

namespace App\Authentication;

use App\Controller\AppController;
use App\Core\UserCache;
use App\Model\Entity\User;
use Authentication\IdentityInterface;
use Authentication\IdentityInterface as AuthenticationInterface;
use Authorization\AuthorizationService;
use Authorization\Exception\Exception;
use Authorization\IdentityInterface as AuthorizationInterface;
use Authorization\Policy\ResultInterface;
use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ActAsIdentity implements AuthenticationInterface, AuthorizationInterface {

	/**
	 * Identity data
	 *
	 * @var \App\Model\Entity\User
	 */
	protected $identity;

	/**
	 * Various permissions. Properties are private and accessed through functions
	 * to prevent any potential security issues with eventual third-party plugins.
	 */
	private bool $_isAdmin = false;
	private bool $_isManager = false;
	private bool $_isCoordinator = false;
	private bool $_isOfficial = false;
	private bool $_isVolunteer = false;
	private bool $_isCoach = false;
	private bool $_isPlayer = false;
	private bool $_isParent = false;
	private bool $_isLoggedIn = false;
	private bool $_isVisitor = true;
	private bool $_isChild = false;

	private array $_managedAffiliateIds = [];
	private array $_coordinatedDivisionIds = [];
	private $_captainedTeamIds = null;
	private $_allCaptainedTeamIds = null;
	private $_teamIds = null;
	private $_allTeamIds = null;
	private $_relativeTeamIds = null;
	private $_allRelativeTeamIds = null;
	private $_relativeIds = null;

	/**
	 * Authorization Service
	 *
	 * @var \Authorization\AuthorizationServiceInterface
	 */
	protected $authorization;

	public function __construct(AuthorizationService $auth, AuthenticationInterface $identity) {
		$this->authorization = $auth;
		$this->identity = $identity->getOriginalData();

		if (is_a($this->identity, \App\Model\Entity\Person::class)) {
			// Handle creating an identity from a person instead of a user.
			// At this time, this is used only in the relative_notice element,
			// which doesn't need user information at all, so we fake it.
			$user = new User();
			$user->person = $this->identity;
			$this->identity = $user;
		}

		$this->_initializeGroups();

		if (!UserCache::identitySet()) {
			UserCache::setIdentity($this->identity);
		}
	}

	protected function _initializeGroups(): void {
		$user_cache = UserCache::getInstance();

		// We explicitly don't save group details in the session, so that any external changes to them will take effect immediately
		$groups = $user_cache->read('Groups', $this->identity->person->id);
		if ($this->identity->real_person) {
			$real_groups = $user_cache->read('Groups', $this->identity->real_person->id);
			if (!empty($real_groups)) {
				$real_group_levels = collection($real_groups)->extract('level')->toArray();
			} else {
				$real_group_levels = [];
			}
			if ($this->identity->real_person->status == 'active') {
				// Approved accounts are granted permissions up to level 1,
				// since they can just add that group to themselves anyway.
				$real_group_levels[] = 1;
			}
			if (empty($real_group_levels)) {
				$max_level = 0;
			} else {
				$max_level = max($real_group_levels);
			}
		}

		foreach ($groups as $group) {
			// Don't give people enhanced access just because the person they are acting as has it
			if ($this->identity->real_person && $group->level > $max_level) {
				continue;
			}

			switch ($group->id) {
				case GROUP_ADMIN:
					if ($this->identity->person->status != 'locked') {
						$this->_isAdmin = $this->_isManager = true;
						$this->_managedAffiliateIds = TableRegistry::getTableLocator()->get('Affiliates')->find()
							->all()
							->extract('id')
							->toArray();
					}
					break;

				case GROUP_MANAGER:
					if ($this->identity->person->status != 'locked') {
						$this->_isManager = true;
						$this->_managedAffiliateIds = $user_cache->read('ManagedAffiliateIDs', $this->identity->person->id);
					}
					break;

				case GROUP_OFFICIAL:
					if ($this->identity->person->status != 'locked') {
						$this->_isOfficial = true;
					}
					break;

				case GROUP_VOLUNTEER:
					if ($this->identity->person->status != 'locked') {
						$this->_isVolunteer = true;
						$this->_coordinatedDivisionIds = $user_cache->read('DivisionIDs', $this->identity->person->id);
						$this->_isCoordinator = !empty($this->_coordinatedDivisionIds);
					}
					break;

				case GROUP_COACH:
					$this->_isCoach = true;
					break;

				case GROUP_PLAYER:
					$this->_isPlayer = true;
					break;

				case GROUP_PARENT:
					$this->_isParent = true;
					break;
			}
		}

		if ($this->identity->person->status != 'locked') {
			$this->_isLoggedIn = true;
			$this->_isVisitor = false;
			$this->_isChild = AppController::_isChild($this->identity->person);
		}
	}

	public function actAs(ServerRequestInterface $request, $target): User {
		if ($this->identity->real_person && $target->id == $this->identity->real_person->id) {
			// We are already acting as someone, and setting it back to ourselves
			$this->identity->person = $this->identity->real_person;
			unset($this->identity->real_person);
		} else if ($target->id != $this->identity->person->id) {
			// We are acting as someone else
			if (!$this->identity->real_person) {
				// We were not previously acting as someone else, so save the real data
				$this->identity->real_person = $this->identity->person;
			}
			$this->identity->person = $target;
		}
		$this->_initializeGroups();

		$request->getAttribute('authentication')->persistIdentity($request, $response, $this->identity);

		return $this->identity;
	}

	/**
	 * Whether a offset exists
	 * @param mixed $offset
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset): bool {
		return $this->identity->has($offset);
	}

	/**
	 * Offset to retrieve
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->identity->get($offset);
	}

	/**
	 * Offset to set
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value): void {
		$this->identity->set($offset, $value);
	}

	/**
	 * Offset to unset
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset): void {
		$this->identity->unset($offset);
	}

	/**
	 * Authentication\IdentityInterface method
	 */
	public function getIdentifier(): int {
		return $this->identity->person->id;
	}

	/**
	 * Authentication\IdentityInterface method
	 */
	public function getOriginalData() {
		return $this->identity;
	}

	/**
	 * Authorization\IdentityInterface method
	 */
	public function can(string $action, $resource): bool {
		return $this->authorization->can($this, $action, $resource);
	}

	public function canResult(string $action, $resource): ResultInterface {
		return $this->authorization->canResult($this, $action, $resource);
	}

	/**
	 * Authorization\IdentityInterface method
	 */
	public function applyScope($action, $resource) {
		return $this->authorization->applyScope($this, $action, $resource);
	}

	public function isAdmin(): bool {
		return $this->_isAdmin;
	}

	public function isManager(): bool {
		if (func_num_args() != 0) {
			throw new InvalidArgumentException('isManager function takes no parameters. Did you mean isManagerOf?');
		}
		return $this->_isManager;
	}

	public function isManagerOf($entity): bool {
		if (empty($this->_managedAffiliateIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$affiliate_id = $entity;
		} else if ($entity->has('affiliate_id')) {
			$affiliate_id = $entity->affiliate_id;
		} else if (is_a($entity, \App\Model\Entity\Affiliate::class)) {
			$affiliate_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\Person::class)) {
			$affiliates = UserCache::getInstance()->read('AffiliateIDs', $entity->id);
			$intersection = array_intersect($this->_managedAffiliateIds, $affiliates);
			return !empty($intersection);
		} else if (is_a($entity, \App\Model\Entity\Note::class) && $entity->person_id) {
			$affiliates = UserCache::getInstance()->read('AffiliateIDs', $entity->person_id);
			$intersection = array_intersect($this->_managedAffiliateIds, $affiliates);
			return !empty($intersection);
		} else if (is_a($entity, \App\Model\Entity\User::class)) {
			if ($entity->has('person')) {
				$person_id = $entity->person->id;
			} else {
				$person_id = TableRegistry::getTableLocator()->get('People')->field('id', ['People.user_id' => $entity->id]);
			}
			$affiliates = UserCache::getInstance()->read('AffiliateIDs', $person_id);
			$intersection = array_intersect($this->_managedAffiliateIds, $affiliates);
			return !empty($intersection);
		} else {
			$affiliate_id = TableRegistry::getTableLocator()->get($entity->getSource())->affiliate($entity->id);
		}

		return in_array($affiliate_id, $this->_managedAffiliateIds);
	}

	public function managedAffiliateIds() {
		return $this->_managedAffiliateIds;
	}

	public function isCoordinator(): bool {
		return $this->_isCoordinator;
	}

	public function isCoordinatorOf($entity): bool {
		if (empty($this->_coordinatedDivisionIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$division_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\League::class)) {
			// Special case to check if a coordinator coordinates all divisions in a league
			if (isset($entity->divisions)) {
				$league_divisions = collection($entity->divisions)->extract('id')->toArray();
			} else {
				$league_divisions = TableRegistry::getTableLocator()->get('Leagues')->divisions($entity);
			}
			$intersection = array_intersect($this->_coordinatedDivisionIds, $league_divisions);
			return (count($league_divisions) == count($intersection));
		} else if (is_a($entity, \App\Model\Entity\Division::class)) {
			$division_id = $entity->id;
		} else if ($entity->has('division_id')) {
			$division_id = $entity->division_id;
		} else {
			try {
				$division_id = TableRegistry::getTableLocator()->get($entity->getSource())->division($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check coordinator of on a non-coordinated entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($division_id, $this->_coordinatedDivisionIds);
	}

	public function coordinatedDivisionIds() {
		return $this->_coordinatedDivisionIds;
	}

	public function isOfficial(): bool {
		return $this->_isOfficial;
	}

	public function isVolunteer(): bool {
		return $this->_isVolunteer;
	}

	public function isCoach(): bool {
		return $this->_isCoach;
	}

	public function isPlayer(): bool {
		return $this->_isPlayer;
	}

	public function isParent(): bool {
		return $this->_isParent;
	}

	public function isLoggedIn(): bool {
		return $this->_isLoggedIn;
	}

	public function isVisitor(): bool {
		return $this->_isVisitor;
	}

	public function isChild(): bool {
		return $this->_isChild;
	}

	public function isCaptainOf($entity): bool {
		if ($this->_captainedTeamIds === null) {
			$this->_captainedTeamIds = UserCache::getInstance()->read('OwnedTeamIDs');
		}

		if (empty($this->_captainedTeamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\Game::class)) {
			return in_array($entity->home_team_id, $this->_captainedTeamIds) || in_array($entity->away_team_id, $this->_captainedTeamIds);
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_captainedTeamIds);
	}

	public function wasCaptainOf($entity): bool {
		if ($this->_allCaptainedTeamIds === null) {
			$this->_allCaptainedTeamIds = UserCache::getInstance()->read('AllOwnedTeamIDs');
		}

		if (empty($this->_allCaptainedTeamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\Game::class)) {
			return in_array($entity->home_team_id, $this->_allCaptainedTeamIds) || in_array($entity->away_team_id, $this->_allCaptainedTeamIds);
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_allCaptainedTeamIds);
	}

	public function isPlayerOn($entity): bool {
		if ($this->_teamIds === null) {
			$this->_teamIds = UserCache::getInstance()->read('AcceptedTeamIDs');
		}

		if (empty($this->_teamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\Game::class)) {
			return in_array($entity->home_team_id, $this->_teamIds) || in_array($entity->away_team_id, $this->_teamIds);
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_teamIds);
	}

	public function wasPlayerOn($entity): bool {
		if ($this->_allTeamIds === null) {
			$this->_allTeamIds = UserCache::getInstance()->read('AllTeamIDs');
		}

		if (empty($this->_allTeamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_allTeamIds);
	}

	public function isRelativePlayerOn($entity): bool {
		if ($this->_relativeTeamIds === null) {
			$this->_relativeTeamIds = UserCache::getInstance()->read('RelativeTeamIDs');
		}

		if (empty($this->_relativeTeamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_relativeTeamIds);
	}

	public function wasRelativePlayerOn($entity): bool {
		if ($this->_allRelativeTeamIds === null) {
			$this->_allRelativeTeamIds = UserCache::getInstance()->read('AllRelativeTeamIDs');
		}

		if (empty($this->_allRelativeTeamIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$team_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Team::class)) {
			$team_id = $entity->id;
		} else if ($entity->has('team_id')) {
			$team_id = $entity->team_id;
		} else {
			try {
				$team_id = TableRegistry::getTableLocator()->get($entity->getSource())->team($entity->id);
			} catch (BadMethodCallException $ex) {
				throw new Exception('Attempt to check team of on a non-team entity type "' . get_class($entity) . '"');
			}
		}

		return in_array($team_id, $this->_allRelativeTeamIds);
	}

	public function isMe($entity): bool {
		if (is_numeric($entity)) {
			$person_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Person::class)) {
			$person_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\User::class)) {
			if ($entity->has('person')) {
				$person_id = $entity->person->id;
			} else {
				$person_id = TableRegistry::getTableLocator()->get('People')->field('id', ['People.user_id' => $entity->id]);
			}
		} else if ($entity && $entity->has('person_id')) {
			$person_id = $entity->person_id;
		} else {
			throw new Exception('Attempt to check isMe on on a non-person entity type "' . get_class($entity) . '"');
		}

		return $this->getIdentifier() == $person_id;
	}

	public function isMine(Entity $entity): bool {
		if ($entity->has('created_person_id')) {
			$person_id = $entity->created_person_id;
		} else {
			throw new Exception('Attempt to check isMine on on a non-owned entity type "' . get_class($entity) . '"');
		}

		return $this->getIdentifier() == $person_id;
	}

	public function isRelative($entity): bool {
		if ($this->_relativeIds === null) {
			$this->_relativeIds = UserCache::getInstance()->read('RelativeIDs');
		}

		if (empty($this->_relativeIds)) {
			return false;
		}

		if (is_numeric($entity)) {
			$person_id = $entity;
		} else if (is_a($entity, \App\Model\Entity\Person::class)) {
			$person_id = $entity->id;
		} else if (is_a($entity, \App\Model\Entity\User::class)) {
			if ($entity->has('person')) {
				$person_id = $entity->person->id;
			} else {
				$person_id = TableRegistry::getTableLocator()->get('People')->field('id', ['People.user_id' => $entity->id]);
			}
		} else if ($entity->has('person_id')) {
			$person_id = $entity->person_id;
		} else {
			throw new Exception('Attempt to check relation on a non-person entity type "' . get_class($entity) . '"');
		}

		return in_array($person_id, $this->_relativeIds);
	}

	public function isRelatives(Entity $entity): bool {
		if ($this->_relativeIds === null) {
			$this->_relativeIds = UserCache::getInstance()->read('RelativeIDs');
		}

		if (empty($this->_relativeIds)) {
			return false;
		}

		if ($entity->has('created_person_id')) {
			$person_id = $entity->created_person_id;
		} else {
			throw new Exception('Attempt to check relation on a non-owned entity type "' . get_class($entity) . '"');
		}

		return in_array($person_id, $this->_relativeIds);
	}

	// Various ways to get the list of affiliates to show
	public function applicableAffiliates($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return [1 => Configure::read('organization.name')];
		}

		$affiliates_table = TableRegistry::getTableLocator()->get('Affiliates');

		// If there's something in the URL, perhaps only use that
		$request = Router::getRequest();
		if ($request) {
			$affiliate = $request->getQuery('affiliate');
			if ($affiliate === null) {
				// If the user has selected a specific affiliate to view, perhaps only use that
				$affiliate = $request->getSession()->read('Zuluru.CurrentAffiliate');
			}
		} else {
			$affiliate = null;
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || $this->_isAdmin || in_array($affiliate, $this->_managedAffiliateIds)) {
				return $affiliates_table->find()
					->enableHydration(false)
					->where(['Affiliates.id' => $affiliate])
					->all()
					->combine('id', 'name')
					->toArray();
			}
		}

		// Managers may get only their list of managed affiliates
		if (!$this->_isAdmin && $this->_isManager && $admin_only) {
			$affiliates = UserCache::getInstance()->read('ManagedAffiliates');
			$affiliates = collection($affiliates)->combine('id', function ($entity) { return $entity->translateField('name'); })->toArray();
			ksort($affiliates);
			return $affiliates;
		}

		// Non-admins get their current list of "subscribed" affiliates
		if ($this->_isLoggedIn && !$this->_isAdmin) {
			$affiliates = UserCache::getInstance()->read('Affiliates');
			if (!empty($affiliates)) {
				$affiliates = collection($affiliates)->combine('id', function ($entity) { return $entity->translateField('name'); })->toArray();
				ksort($affiliates);
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		return $affiliates_table->find()
			->enableHydration(false)
			->where(['Affiliates.active' => true])
			->order('Affiliates.name')
			->all()
			->combine('id', 'name')
			->toArray();
	}

	// Various ways to get the list of affiliates to query
	public function applicableAffiliateIDs($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return [1];
		}

		// If there's something in the URL, perhaps only use that
		$request = Router::getRequest();
		if ($request) {
			$affiliate = $request->getQuery('affiliate');
			if ($affiliate === null) {
				// If the user has selected a specific affiliate to view, perhaps only use that
				$affiliate = $request->getSession()->read('Zuluru.CurrentAffiliate');
			}
		} else {
			$affiliate = null;
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || $this->_isAdmin || in_array($affiliate, $this->_managedAffiliateIds)) {
				return [$affiliate];
			}
		}

		// Managers may get only their list of managed affiliates
		if (!$this->_isAdmin && $this->_isManager && $admin_only) {
			return $this->_managedAffiliateIds;
		}

		// Non-admins get their current list of "subscribed" affiliates
		if ($this->_isLoggedIn && !$this->_isAdmin) {
			$affiliates = UserCache::getInstance()->read('AffiliateIDs');
			if (!empty($affiliates)) {
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		return array_keys(TableRegistry::getTableLocator()->get('Affiliates')->find()
			->enableHydration(false)
			->where(['Affiliates.active' => true])
			->order('Affiliates.name')
			->all()
			->combine('id', 'name')
			->toArray()
		);
	}

}
