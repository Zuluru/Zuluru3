<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Core\UserCache;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Event;
use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class EventPolicy extends AppPolicy {

	// Cached versions of some data, for when we call canRegister() again and again
	private $_person = null;
	private $_person_id = null;
	private $_person_duplicates = null;

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		$this->blockAnonymousExcept($identity, $action, ['wizard']);
		$this->blockLocked($identity);
	}

	public function canWizard(IdentityInterface $identity = null, $resource) {
		if (!$identity || !$identity->isLoggedIn()) {
			throw new ForbiddenRedirectException(null, ['controller' => 'Events', 'action' => 'index']);
		}

		return true;
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Event $event) {
		return $identity->isManagerOf($event);
	}

	public function canEvent_type_fields(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd_price(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd_preregistration(IdentityInterface $identity, Event $event) {
		return $identity->isManagerOf($event);
	}

	public function canFull_list(IdentityInterface $identity, Event $event) {
		return $identity->isManagerOf($event) || $identity->isCoordinatorOf($event);
	}

	public function canSummary(IdentityInterface $identity, Event $event) {
		return $identity->isManagerOf($event) || $identity->isCoordinatorOf($event);
	}

	public function canWaiting(IdentityInterface $identity, Event $event) {
		if (!Configure::read('feature.waiting_list')) {
			throw new ForbiddenRedirectException(__('Waiting lists are not enabled on this site.'));
		}

		return $identity->isManagerOf($event) || $identity->isCoordinatorOf($event);
	}

	public function canRefund(IdentityInterface $identity, Event $event) {
		return $identity->isManagerOf($event);
	}

	/**
	 * Test whether a user is allowed to register for something.
	 *
	 * $resource options controlling the returned data:
	 *		'ignore_date': If true, ignore open and close dates (default false)
	 *		'strict': If false, allow things with prerequisites that are not yet filled but can easily be (default true)
	 *		'waiting' If true, ignore the cap to allow waiting list registrations (default false)
	 *		'all_rules': If true, test tries all price points instead of exiting as soon as an allowed one is found (default false)
	 *		'for_edit': If we are editing a registration, this is set to that registration, so skip tests related to already being registered (default false)
	 *
	 * If the registration cannot proceed, the resource will have notices and redirect properties added to it.
	 */
	public function canRegister(IdentityInterface $identity, ContextResource $resource) {
		// Set some default options, where null doesn't quite cut it
		if (!$resource->person_id) {
			$resource->person_id = $identity->getIdentifier();
		}
		if (!$resource->has('strict')) {
			$resource->strict = true;
		}
		if (!$resource->waiting || !Configure::read('feature.waiting_list')) {
			$resource->waiting = false;
		}

		$resource->notices = $this->_testCanRegister($identity, $resource);

		// May need to copy canRegister info from the provided price into the main list
		$event = $resource->resource();
		if ($resource->price && (!empty($event->prices))) {
			collection($event->prices)->firstMatch(['id' => $resource->price->id])->canRegister = $resource->price->canRegister;
		}

		$redirect = collection($resource->notices)->filter(function ($notice) {
			return !empty($notice['redirect']);
		})->toArray();
		if (!empty($redirect)) {
			$resource->redirect = current($redirect)['redirect'];
		}

		if (collection($resource->notices)->some(function ($notice) {
			return !empty($notice['allowed']);
		})) {
			return true;
		}

		throw new ForbiddenRedirectException('{0}',
			$redirect ? $resource->redirect : ['controller' => 'Events', 'action' => 'wizard'],
			'html', ['params' => ['replacements' => $resource->notices, 'class' => 'warning']]);
	}

	private function _testCanRegister(IdentityInterface $identity, ContextResource $resource) {
		$userCache = UserCache::getInstance();

		// Get everything from the user record that the decisions below might need
		$person_id = $resource->person_id;
		if ($person_id != $this->_person_id) {
			$this->_person_id = $person_id;
			$this->_person = $userCache->read('Person', $person_id);
			$this->_person->group_ids = $userCache->read('GroupIDs', $person_id);
			$this->_person->teams = $userCache->read('AllTeams', $person_id);
			$this->_person->preregistrations = $userCache->read('Preregistrations', $person_id);
			$this->_person->registrations = array_merge(
				$userCache->read('RegistrationsPaid', $person_id),
				$userCache->read('RegistrationsUnpaid', $person_id)
			);
			$this->_person->uploads = $userCache->read('Documents', $person_id);
			$this->_person->affiliates = $userCache->read('Affiliates', $person_id);
			$this->_person->waivers = $userCache->read('Waivers', $person_id);
			$this->_person_duplicates = null;
		}

		// TODO: Eliminate hard-coded event types
		$event = $resource->resource();
		$event_type = $event->event_type;
		if ((!is_array($this->_person->group_ids) || !in_array(GROUP_PLAYER, $this->_person->group_ids)) && in_array($event_type->type, ['membership', 'individual'])) {
			return [[
				'text' => __('Only players are allowed to register for this type of event.'),
				'class' => 'warning-message',
			]];
		}

		// Check whether this user is considered new or inactive for the purposes of registration
		$is_new = ($this->_person->status == 'new');
		$is_inactive = ($this->_person->status == 'inactive');
		// If the user is not yet approved, we may let them register but not pay
		if ($is_new && Configure::read('registration.allow_tentative')) {
			if ($this->_person_duplicates === null) {
				$this->_person_duplicates = TableRegistry::getTableLocator()->get('People')->find('duplicates', ['person' => $this->_person]);
			}
			if ($this->_person_duplicates->count() == 0) {
				$is_new = false;
			}
		}
		if ($is_new) {
			return [[
				'text' => __('You are not allowed to register for events until your profile has been approved by an administrator.') . ' ' .
					__('This normally happens within one business day, and often in just a few minutes.'),
				'class' => 'warning-message',
			]];
		}

		if ($is_inactive) {
			return [[
				'format' => __('You are not allowed to register for events until you {0}.'),
				'replacements' => [[
					'type' => 'link',
					'link' => __('reactivate your profile'),
					'target' => ['controller' => 'People', 'action' => 'reactivate', 'return' => AppController::_return()],
				]],
				'class' => 'warning-message',
			]];
		}

		// If we're editing a registration, remove it from the user's list,
		// as it only causes problems with rules in the CanRegister test
		if ($resource->for_edit) {
			$this->_person->registrations = collection($this->_person->registrations)->reject(function ($registration) use ($resource) {
				return $registration->id == $resource->for_edit->id;
			})->toList();
		}

		// Pull out the registration record(s) for the current event, if any.
		if (!empty($this->_person->registrations)) {
			$registrations = collection($this->_person->registrations)->match(['event_id' => $event->id])->toList();
			$is_registered = !empty($registrations);
		} else {
			$is_registered = false;
		}

		// Some tests based on whether the person has already registered for this.
		$notices = [];
		if ($is_registered && !$resource->for_edit) {
			if ($registrations[0]->payment == 'Paid') {
				$notices[] = [
					'text' => __('You have already registered and paid for this event.'),
					'class' => 'open',
				];
			} else if (Configure::read('feature.waiting_list') && $registrations[0]->payment == 'Waiting') {
				$notices[] = [
					'text' => __('You have already been added to the waiting list for this event.'),
					'class' => 'open',
				];
			} else {
				$notices[] = [
					'text' => __('You have already registered for this event, but not yet paid.'),
					'class' => 'warning-message',
				];
				$notices[] = [
					'format' => __('To complete your payment, please proceed to the {0}.'),
					'replacements' => [[
						'type' => 'link',
						'link' => __('checkout page'),
						'target' => ['controller' => 'Registrations', 'action' => 'checkout'],
					]],
				];
				$notices[] = [
					'format' => __('If you registered in error or have changed your mind about participating, you can remove this from your {0}.'),
					'replacements' => [[
						'type' => 'link',
						'link' => __('registration list'),
						'target' => ['controller' => 'Registrations', 'action' => 'checkout'],
					]],
				];
			}

			if (!$event->multiple) {
				return $notices;
			}
		}

		if (!$resource->for_edit) {
			// Find the registration cap and how many are already registered.
			$cap = $event->cap($this->_person->roster_designation);
			if ($cap != CAP_UNLIMITED) {
				$paid = $event->count($this->_person->roster_designation);
			}

			if ($cap == 0) {
				// 0 means that nobody of this gender is allowed.
				return [[
					'text' => __('This event is for the opposite gender only.'),
					'class' => 'error-message',
				]];
			} else if ($cap > 0 && !$resource->waiting) {
				// Check if this event is already full
				// -1 means there is no cap, so don't check in that case.
				if ($paid >= $cap) {
					if (Configure::read('feature.waiting_list')) {
						$notices[] = [
							'format' => __('This event is already full. You may however {0} to be put on a waiting list in case others drop out.'),
							'replacements' => [[
								'type' => 'link',
								'link' => __('continue with registration'),
								'target' => ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id, 'waiting' => true],
							]],
							'class' => 'highlight-message'
						];
					} else {
						$notices[] = [
							'text' => __('This event is already full.'),
							'class' => 'highlight-message',
						];
					}
					return $notices;
				}
			}
		}

		// If they are already registered, the only way we got here is if multiples are allowed. Note that now.
		if ($is_registered) {
			$notices[] = [
				'text' => __('This event allows multiple registrations (e.g. the same person can register teams to play on different nights).'),
				'class' => 'open',
			];
		}

		// Check each price point
		if ($resource->price) {
			$prices = [$resource->price];
		} else if (!empty($event->prices)) {
			$prices = $event->prices;
		}

		// If there is a preregistration record, we ignore open and close times.
		$prereg = collection($this->_person->preregistrations)->some(function ($prereg) use ($event) {
			return $prereg->event_id == $event->id;
		});

		$rule_obj = ModuleRegistry::getInstance()->load('RuleEngine');
		foreach ($prices as $price) {
			$name = empty($price->name) ? __('this event') : $price->name;

			if (!$prereg && !$resource->ignore_date) {
				// Admins can test registration before it opens...
				if ($price->open->isFuture() && (!$identity || !$identity->isManagerOf($event))) {
					$price->canRegister = [
						'allowed' => false,
						'text' => __('Registration for {0} is not yet open.', $name),
						'class' => 'closed',
					];
					continue;
				}
				if ($price->close->isPast()) {
					$price->canRegister = [
						'allowed' => false,
						'text' => __('Registration for {0} has closed.', $name),
						'class' => 'closed',
					];
					continue;
				}
			}

			// Check the registration rule, if any
			if (!empty($price->register_rule)) {
				if (!$rule_obj->init($price->register_rule)) {
					$price->canRegister = [
						'allowed' => false,
						'text' => __('Failed to parse the rule: {0}', $rule_obj->parse_error),
						'class' => 'error-message',
					];
				} else {
					$price->canRegister = [
						'allowed' => $rule_obj->evaluate($event->affiliate_id, $this->_person, null, $resource->strict, false),
					];
					if ($price->canRegister['allowed']) {
						$price->canRegister['text'] = __('You may register for {0}.', $name);
						if (!$resource->all_rules) {
							break;
						}
					} else {
						$price->canRegister['format'] = __('To register for {0}, you must {1}.');
						$price->canRegister['replacements'] = [$name, $rule_obj->reason];
						$price->canRegister['class'] = 'error-message';
						if ($resource->strict && count($prices) == 1) {
							$price->canRegister['redirect'] = $rule_obj->redirect;
						}
					}
				}
			} else {
				$price->canRegister = [
					'allowed' => true,
					'text' => __('You may register for this because there are no prerequisites.'),
				];
			}
		}

		// We checked earlier that there is at least one price point currently applicable,
		// which means that at least one thing went through the rule check above.
		$report_on = collection($prices)->match(['canRegister.allowed' => true]);
		if ($report_on->isEmpty()) {
			$report_on = collection($prices);
		}

		if (iterator_count($report_on) == 1) {
			$notices[] = $report_on->first()->canRegister;
		} else {
			$reasons = array_merge(
				array_unique($report_on->reject(function($price) {
					return empty($price->canRegister['text']);
				})->extract('canRegister.text')->toArray()),
				array_unique($report_on->reject(function($price) {
					return empty($price->canRegister['format']);
				})->extract('canRegister.format')->toArray())
			);
			if (count($reasons) == 1) {
				// Every price will have this record. If they're all the same, we can safely just use the first one.
				$notices[] = $report_on->first()->canRegister;
			} else {
				foreach ($report_on as $price) {
					$notices[] = $price->canRegister;
				}
			}
		}

		return $notices;
	}

}
