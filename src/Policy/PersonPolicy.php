<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Core\UserCache;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Person;
use App\PasswordHasher\HasherTrait;
use Authorization\Exception\MissingIdentityException;
use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\GoneException;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

class PersonPolicy extends AppPolicy {

	use HasherTrait;

	public function before($identity, $resource, $action) {
		$this->blockAnonymousExcept($identity, $action, ['view', 'tooltip', 'approve_relative', 'remove_relative', 'vcf', 'ical']);
		$this->blockLockedExcept($identity, $action, ['act_as']);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canStatistics(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDemographics(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canParticipation(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canRetention(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDisplay_legal_names(IdentityInterface $identity, $controller) {
		return Configure::read('profile.legal_name') && $identity->isManager();
	}

	public function canView(IdentityInterface $identity = null, Person $person) {
		if ($person->status == 'inactive' && (!$identity || !$identity->isLoggedIn())) {
			throw new GoneException();
		}

		return true;
	}

	public function canView_tasks(IdentityInterface $identity, Person $person) {
		return Configure::read('feature.tasks') &&
			($identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person));
	}

	public function canTooltip(IdentityInterface $identity = null, Person $person) {
		if ($person->status == 'inactive' && (!$identity || !$identity->isLoggedIn())) {
			return false;
		}

		return true;
	}

	public function canEdit(IdentityInterface $identity, Person $person) {
		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canDeactivate(IdentityInterface $identity, Person $person) {
		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canReactivate(IdentityInterface $identity, Person $person) {
		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canConfirm(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canNote(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.annotations')) {
			return false;
		}

		return true;
	}

	public function canPreferences(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canAdd_relative(IdentityInterface $identity, $controller) {
		return $identity->isParent();
	}

	public function canLink_relative(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canApprove_relative(IdentityInterface $identity = null, ContextResource $resource) {
		$person = $resource->resource();

		if (!$resource->has('relation')) {
			throw new ForbiddenRedirectException(__('This person does not have an outstanding relative request for you.'),
				['action' => 'view', 'person' => $person->id]);
		}

		// Authenticate the hash code
		$relation = $resource->relation;
		if ($resource->has('code')) {
			$code = $resource->code;
			if (!$this->_checkHash([$relation->_joinData->id, $relation->_joinData->person_id, $relation->_joinData->relative_id, $relation->_joinData->created], $code)) {
				throw new ForbiddenRedirectException(__('The authorization code is invalid.'),
					['action' => 'view', 'person' => $person->id]);
			}

			return true;
		}

		// If there wasn't a code, then anyone not logged in cannot proceed
		if (!$identity) {
			throw new MissingIdentityException();
		}

		return $identity->isManagerOf($person) || $identity->isMe($person);
	}

	public function canRemove_relative(IdentityInterface $identity = null, ContextResource $resource) {
		$person = $resource->resource();

		if (!$resource->has('relation')) {
			// Use the authorization code error here, so as not to leak any information about who is related to whom
			throw new ForbiddenRedirectException(__('The authorization code is invalid.'),
				['action' => 'view', 'person' => $person->id]);
		}

		// Authenticate the hash code
		$relation = $resource->relation;
		if ($resource->has('code')) {
			$code = $resource->code;
			if (!$this->_checkHash([$relation->_joinData->id, $relation->_joinData->person_id, $relation->_joinData->relative_id, $relation->_joinData->created], $code)) {
				throw new ForbiddenRedirectException(__('The authorization code is invalid.'),
					['action' => 'view', 'person' => $person->id]);
			}

			return true;
		}

		// If there wasn't a code, then anyone not logged in cannot proceed
		if (!$identity) {
			throw new MissingIdentityException();
		}

		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canView_contacts(IdentityInterface $identity, Person $person) {
		return AppController::_isChild($person) && ($identity->isManagerOf($person) || $identity->isMe($person));
	}

	public function canAuthorize_twitter(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canRevoke_twitter(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canPhoto(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.photos')) {
			return false;
		}

		return true;
	}

	public function canPhoto_upload(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.photos')) {
			return false;
		}

		return true;
	}

	public function canApprove_photos(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.photos')) {
			return false;
		}

		return $identity->isManager();
	}

	public function canApprove_photo(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.photos')) {
			return false;
		}

		return $identity->isManagerOf($person);
	}

	public function canDelete_photo(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.photos')) {
			return false;
		}

		return $identity->isManagerOf($person);
	}

	public function canDocument_upload(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.documents')) {
			return false;
		}

		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canApprove_documents(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.documents')) {
			return false;
		}

		return $identity->isManager();
	}

	public function canNominate(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.badges')) {
			return false;
		}

		return true;
	}

	public function canApprove_badges(IdentityInterface $identity, $controller) {
		if (!Configure::read('feature.badges')) {
			return false;
		}

		return $identity->isManager();
	}

	public function canDelete(IdentityInterface $identity, Person $person) {
		return $identity->isManagerOf($person);
	}

	public function canSplash(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canSchedule(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canConsolidated_schedule(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canAct_as(IdentityInterface $identity, Person $person) {
		// People can always act as their real id, or as any relative of the current or real user
		$relatives = UserCache::getInstance()->allActAs();
		if ($identity->isMe($person) || array_key_exists($person->id, $relatives)) {
			return true;
		}

		if ($identity->isAdmin()) {
			if (in_array(GROUP_ADMIN, UserCache::getInstance()->read('GroupIDs', $person->id))) {
				throw new ForbiddenRedirectException(__('Administrators cannot act as other administrators.'));
			}
			return true;
		} else if ($identity->isManager()) {
			$intersect = array_intersect([GROUP_ADMIN, GROUP_MANAGER], UserCache::getInstance()->read('GroupIDs', $person->id));
			if (!empty($intersect)) {
				throw new ForbiddenRedirectException(__('Managers cannot act as other managers.'));
			}
			return $identity->isManagerOf($person);
		}

		return false;
	}

	public function canAct_as_select(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canSearch(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canRule_search(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canLeague_search(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canInactive_search(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canList_new(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canApprove(IdentityInterface $identity, Person $person) {
		if ($person->status != 'new') {
			throw new ForbiddenRedirectException(__('That account has already been approved.'), ['controller' => 'People', 'action' => 'list_new']);
		}

		return $identity->isManagerOf($person);
	}

	public function canVcf(IdentityInterface $identity = null, Person $person) {
		if ($person->status == 'inactive' && (!$identity || !$identity->isLoggedIn())) {
			throw new GoneException();
		}

		return true;
	}

	public function canIcal(IdentityInterface $identity = null, Person $person) {
		if (empty($person->settings) || !$person->settings[0]->value) {
			throw new GoneException();
		}

		if ($person->status == 'inactive' && (!$identity || !$identity->isLoggedIn())) {
			throw new GoneException();
		}

		return true;
	}

	public function canRegistrations(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canCredits(IdentityInterface $identity, Person $person) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canTeams(IdentityInterface $identity, Person $person) {
		return true;
	}

	public function canWaivers(IdentityInterface $identity, Person $person) {
		return $identity->isManagerOf($person) || $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canFuture(IdentityInterface $identity, Person $person) {
		return $identity->isMe($person) || $identity->isRelative($person);
	}

	public function canShow_registration(IdentityInterface $identity = null, $controller) {
		if (!$identity || !$identity->isLoggedIn() ||
			$identity->isManager() || $identity->isPlayer() || $identity->isParent() ||
			!empty(UserCache::getInstance()->read('Registrations', $identity->getIdentifier()))
		) {
			return true;
		}

		// If there are any generic events available, everyone gets it
		$affiliates = $identity->applicableAffiliateIDs();
		$events_table = TableRegistry::getTableLocator()->get('Events');
		if ($events_table->find()
				->contain('EventTypes')
				->where([
					'EventTypes.type' => 'generic',
					'Events.open <' => FrozenDate::now()->addDays(30),
					'Events.close >' => FrozenDate::now(),
					'Events.affiliate_id IN' => $affiliates,
				])
				->count() > 0)
		{
			return true;
		}

		// If there are any team events available, coaches get it
		if ($identity->isCoach()) {
			if ($events_table->find()
					->contain('EventTypes')
					->where([
						'EventTypes.type' => 'team',
						'Events.open <' => FrozenDate::now()->addDays(30),
						'Events.close >' => FrozenDate::now(),
						'Events.affiliate_id IN' => $affiliates,
					])
					->count() > 0)
			{
				return true;
			}
		}

		return false;
	}

}
