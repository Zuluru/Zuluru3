<?php
namespace App\Controller;

use App\Cache\Cache;
use App\Core\UserCache;
use App\Core\ModuleRegistry;
use App\Model\Entity\Person;
use App\Mailer\Email;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\I18n\Number;
use Cake\Log\Log;
use Cake\Network\Exception\SocketException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Exception;
use Muffin\Footprint\Auth\FootprintAwareTrait;
use Psr\Log\LogLevel;

/**
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.controller
 * @property \App\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \App\Core\UserCache $UserCache
 * @property \App\Model\Table\ConfigurationTable $Configuration
 */
class AppController extends Controller {
	use FootprintAwareTrait {
		_setCurrentUser as _footprintSetCurrentUser;
	}

	protected $menu_items = [];

	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like loading components.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function initialize() {
		parent::initialize();

		// TODO: Find a better solution for black-holing of Ajax requests?
		if (!$this->request->is('ajax') && !$this->request->is('json')) {
			$this->loadComponent('Security');
			$this->Security->config('blackHoleCallback', '_blackhole');
		}

		$this->loadComponent('Flash');
		$this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);

		// Don't attempt to do anything database- or user-related during installation
		if ($this->plugin == 'Installer') {
			return;
		}

		$this->UserCache = UserCache::getInstance(true);
		$this->moduleRegistry = new ModuleRegistry();

		$this->loadComponent('Authentication', [
			'logoutRedirect' => '/',
			'loginRedirect' => '/',
		]);

		// Check what actions anyone (logged on or not) is allowed in this controller.
		$allowed = $this->request->is('json') ? $this->_noAuthenticationJsonActions() : $this->_noAuthenticationActions();
		$this->Authentication->allowUnauthenticated($allowed);

		$this->loadComponent('Authorization.Authorization');

		// Footprint trait needs the _userModel set to whatever is being used for authentication
		$this->_userModel = Configure::read('Security.authModel');

		// Set the default format for converting Time and Date objects to strings,
		// so that it matches the SQL format that we use for comparing.
		\Cake\I18n\FrozenTime::setToStringFormat('yyyy-MM-dd HH:mm:ss');
		\Cake\I18n\FrozenDate::setToStringFormat('yyyy-MM-dd');

		$identity = $this->Authentication->getIdentity();
		if ($identity) {
			$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $this->_userModel);
			if ($users_table->manageUsers) {
				$user = $identity->getOriginalData();
				$this->UserCache->clear('User', $user->person->id);

				$user->last_login = FrozenTime::now();
				$this->request->trustProxy = true;
				$user->client_ip = $this->request->clientIp();

				$identifiers = $this->Authentication->getAuthenticationService()->identifiers();
				foreach ($identifiers as $identifier) {
					if (method_exists($identifier, 'needsPasswordRehash') && $identifier->needsPasswordRehash()) {
						$user->password = $this->request->getData('password');
						break;
					}
				}

				// Nothing useful to do if this save fails; they still log in, we just don't get an update of the IP and time.
				// We do NOT want to update the act-as profile's user_id with the real user's!
				$users_table->save($user, ['checkRules' => false, 'associated' => false]);
			}
		}
	}

	public function beforeFilter(CakeEvent $cakeEvent) {
		parent::beforeFilter($cakeEvent);

		// Don't attempt to do anything database- or user-related during installation
		if ($this->plugin == 'Installer') {
			return;
		}

		// Backward compatibility with old CakePHP-style named URLs
		// TODO: Remove this in maybe 2020, once there's been a good time to update links
		$this->request = Router::parseNamedParams($this->request);
		$this->request = $this->request->withQueryParams(array_merge($this->request->getQueryParams(), $this->request->getParam('named')));

		$this->loadModel('Configuration');
		$this->_setLanguage();

		// Load configuration from database or cache
		$this->Configuration->loadUser($this->UserCache->currentId());

		// The flash mailer transport works by triggering an event. It needs to be handled somewhere that has access to the Flash component.
		EventManager::instance()->on('Mailer.Transport.flash', [$this, 'flashEmail']);

		EventManager::instance()->off('Flash');
		EventManager::instance()->on('Flash', [$this, 'flash']);

		if (Configure::read('feature.items_per_page')) {
			$this->paginate = array_merge($this->paginate,
				['limit' => Configure::read('feature.items_per_page')]
			);
		}

		$this->request->addDetector('csv', ['param' => '_ext', 'value' => 'csv']);
		$this->response->type(['csv' => 'text/x-csv']);

		// Check if we need to redirect logged-in users for some required step first
		// Don't cause redirects for JSON requests.
		$free = $this->_freeActions();
		$identity = $this->Authentication->getIdentity();
		if (!$this->request->is('json') && $identity && $identity->isLoggedIn() && !in_array($this->request->action, $free)) {
			if ($this->UserCache->read('Person.user_id') && empty($this->UserCache->read('Person.email'))) {
				$this->Flash->warning(__('Last time we tried to contact you, your email bounced. We require a valid email address as part of your profile. You must update it before proceeding.'));
				$this->Authorization->skipAuthorization();
				return $this->forceRedirect(['plugin' => false, 'controller' => 'People', 'action' => 'edit']);
			}

			if ($this->UserCache->read('Person.complete') == 0) {
				$this->Flash->warning(__('Your profile is incomplete. You must update it before proceeding.'));
				$this->Authorization->skipAuthorization();
				return $this->forceRedirect(['plugin' => false, 'controller' => 'People', 'action' => 'edit']);
			}

			// Force response to roster requests, if enabled
			if (Configure::read('feature.force_roster_request')) {
				$response_required = collection($this->UserCache->read('Teams'))->filter(function ($team) {
					return $team->_matchingData['TeamsPeople']->status == ROSTER_INVITED &&
						// Only force responses to leagues that have started play, but the roster deadline hasn't passed
						$team->division->open->isPast() && !$team->division->roster_deadline_passed;
				})->extract('id')->toArray();
				if (!empty($response_required) &&
					// Let's not block admins from turning off this setting if they have a team request
					$this->request->getParam('controller') != 'Settings' &&
					// We will let people look at information about teams that they've been invited to
					($this->request->getParam('controller') != 'Teams' || !in_array($this->request->getQuery('team'), $response_required))
				) {
					$this->Flash->info(__('You have been invited to join a team, and must either accept or decline this invitation before proceeding. Before deciding, you have the ability to look at this team\'s roster, schedule, etc.'));
					return $this->redirect(['plugin' => false, 'controller' => 'Teams', 'action' => 'view', 'team' => current($response_required)]);
				}
			}
		}

		// TODOLATER: If all of the _addXxxMenuItems functions go away, we can probably move this to
		// beforeRender and eliminate the duplicated call that happens in PeopleController::preferences.
		$this->_initMenu();
	}

	public function flashEmail(CakeEvent $event, Email $email, $result) {
		$this->Flash->email(null, [
			'params' => [
				'saved' => true,
				'to' => $email->to(),
				'from' => $email->from(),
				'replyTo' => $email->replyTo(),
				'cc' => $email->cc(),
				'bcc' => $email->bcc(),
				'subject' => $email->subject(),
				'result' => $result,
			],
			'key' => 'email',
		]);
	}

	public function flash(CakeEvent $event, $category, $message, $options = []) {
		$this->Flash->$category($message, $options);
	}

	protected function _setLanguage() {
		Number::defaultCurrency(Configure::read('payment.currency'));
		Configure::load('options', 'default', false);
		Configure::load('sports', 'default', false);
		$configuration_table = TableRegistry::getTableLocator()->get('Configuration');
		$configuration_table->loadSystem();

		$field = env('FIELD_NAME', 'field');
		Configure::write('UI', [
			'field' => __($field),
			'field_cap' => __(Inflector::humanize($field)),
			'fields' => __(Inflector::pluralize($field)),
			'fields_cap' => __(Inflector::humanize(Inflector::pluralize($field))),
		]);
	}

	public function beforeRender(CakeEvent $cakeEvent) {
		parent::beforeRender($cakeEvent);

		if ($this->request->is('json')) {
			// We don't have our own JSON view class to set these overrides
			$this->viewBuilder()->helpers([
				'Html' => ['className' => 'ZuluruHtml'],
				'Time' => ['className' => 'ZuluruTime'],
			]);
		} else {
			// Set view variables for the menu
			// TODO: Get the menu element name from some configuration. Probably a lot more to do to make it all non-Bootstrap-specific
			$this->set('menu_element', 'bootstrap');
			$this->set('menu_items', $this->menu_items);
		}
	}

	/**
	 * Redirects to given $url, after turning off $this->autoRender, unless there is a "return" referer to go to instead.
	 *
	 * @param string|array $url A string or array-based URL pointing to another location within the app,
	 *     or an absolute URL
	 * @param int $status HTTP status code (eg: 301)
	 * @return \Cake\Http\Response|null
	 * @link https://book.cakephp.org/3/en/controllers.html#Controller::redirect
	 */
	public function redirect($url, $status = 302) {
		$this->autoRender = false;

		if ($status) {
			$this->response = $this->response->withStatus($status);
		}

		if ($this->request->getQuery('return')) {
			// If there's a return requested, and nothing already saved to return to, remember the referrer
			$url = $this->decodeRedirect($this->request->getQuery('return'));
		} else if ($this->request->getQuery('redirect')) {
			$url = $this->request->getQuery('redirect');
		}

		// String URLs might have come from $this->here, or might be '/'.
		// Either way, they need to be normalized.
		if (is_string($url)) {
			$url = Router::normalize($url);
		}

		$event = $this->dispatchEvent('Controller.beforeRedirect', [$url, $this->response]);
		if ($event->getResult() instanceof Response) {
			return $this->response = $event->getResult();
		}
		if ($event->isStopped()) {
			return null;
		}
		$response = $this->response;

		if (!$response->getHeaderLine('Location')) {
			$response = $response->withLocation(Router::url($url));
		}

		return $this->response = $response;
	}

	/**
	 * Redirects to given $url, regardless of any query parameter.
	 * Script execution is halted after the redirect.
	 *
	 * @param string|array $url A string or array-based URL pointing to another location within the app,
	 *     or an absolute URL
	 * @param int $status HTTP status code (eg: 301)
	 * @return \Cake\Http\Response|null
	 * @link http://book.cakephp.org/3.0/en/controllers.html#Controller::redirect
	 */
	public function forceRedirect($url, $status = 302) {
		$params = $this->request->getQueryParams();
		unset($params['redirect']);
		unset($params['return']);
		$this->request = $this->request->withQueryParams($params);

		return $this->redirect($url, $status);
	}

	private function decodeRedirect($url) {
		$url = \App\Lib\base64_url_decode($url);

		if (strpos($url, '?') !== false) {
			[$short_url, $querystr] = explode('?', $url);
			parse_str($querystr, $queryArgs);
			if (array_key_exists('act_as', $queryArgs)) {
				// Remove act_as from the list of arguments, to prevent people from going back to whoever
				// they were acting as.
				unset($queryArgs['act_as']);
				if (!empty($queryArgs)) {
					$url = $short_url . '?' . http_build_query($queryArgs);
				}
			}
		}

		return $url;
	}

	/**
	 * Read and set variables for the database-based address options.
	 */
	protected function _loadAddressOptions() {
		$this->set([
			'provinces' => Configure::read('provinces'),
			'countries' => Configure::read('countries'),
		]);
	}

	/**
	 * Read and set variables for the database-based affiliate options.
	 */
	protected function _loadAffiliateOptions() {
		$this->loadModel('Affiliates');

		$conditions = [
			'Affiliates.active' => true,
		];
		$managed = $this->UserCache->read('ManagedAffiliateIDs');
		if (!empty($managed)) {
			$conditions['NOT'] = [
				'Affiliates.id IN' => $managed,
			];
		}

		$affiliates = $this->Affiliates->find('list', compact('conditions'))->toArray();

		$this->set(compact('affiliates'));
	}

	/**
	 * _noAuthenticationActions method
	 *
	 * By default, nothing is available to unauthenticated users.
	 * Any controller with special permissions must override this function.
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return [];
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * By default, nothing is available to unauthenticated users.
	 * Any controller with special permissions must override this function.
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return [];
	}

	/**
	 * _freeActions method
	 *
	 * Some actions must always be allowed regardless of any redirect that we might want.
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return [];
	}

	/**
	 * Put basic items on the menu, some based on configuration settings.
	 * Other items like specific teams and divisions are added elsewhere.
	 */
	protected function _initMenu() {
		// Initialize the menu
		$this->menu_items = [];

		$identity = $this->Authentication->getIdentity();
		$groups = $this->UserCache->read('GroupIDs');
		if ($identity && $identity->isManager()) {
			$affiliates = $this->Authentication->applicableAffiliates(true);
		}

		if ($this->UserCache->currentId()) {
			$this->_addMenuItem(__('Dashboard'), ['plugin' => false, 'controller' => 'People', 'action' => 'splash']);
		}

		if ($identity && $identity->isLoggedIn()) {
			$this->_addMenuItem(__('My Profile'), ['plugin' => false, 'controller' => 'People', 'action' => 'view']);
			$this->_addMenuItem(__('View'), ['plugin' => false, 'controller' => 'People', 'action' => 'view'], __('My Profile'));
			$this->_addMenuItem(__('Edit'), ['plugin' => false, 'controller' => 'People', 'action' => 'edit'], __('My Profile'));
			if (!$identity->getOriginalData()->person->user_id) {
				$this->_addMenuItem(__('Create Login'), ['plugin' => false, 'controller' => 'People', 'action' => 'add_account'], __('My Profile'));
			}
			$this->_addMenuItem(__('Preferences'), ['plugin' => false, 'controller' => 'People', 'action' => 'preferences'], __('My Profile'));
			if (in_array(GROUP_PARENT, $groups)) {
				$this->_addMenuItem(__('Add New Child'), ['plugin' => false, 'controller' => 'People', 'action' => 'add_relative'], __('My Profile'));
			}
			$this->_addMenuItem(__('Link to Relative'), ['plugin' => false, 'controller' => 'People', 'action' => 'link_relative'], __('My Profile'));
			if (in_array(GROUP_PLAYER, $groups)) {
				$this->_addMenuItem(__('Waiver History'), ['plugin' => false, 'controller' => 'People', 'action' => 'waivers'], __('My Profile'));
			}
			if ($this->UserCache->read('Person.user_id')) {
				$this->_addMenuItem(__('Change Password'), ['plugin' => false, 'controller' => 'Users', 'action' => 'change_password'], __('My Profile'));
			}
			$status = $this->UserCache->read('Person.status');
			if ($status == 'active') {
				$this->_addMenuItem(__('Deactivate'), ['plugin' => false, 'controller' => 'People', 'action' => 'deactivate'], __('My Profile'));
			} else if ($status == 'inactive') {
				$this->_addMenuItem(__('Reactivate'), ['plugin' => false, 'controller' => 'People', 'action' => 'reactivate'], __('My Profile'));
			}
			if (Configure::read('feature.photos')) {
				$this->_addMenuItem(__('Upload Photo'), ['plugin' => false, 'controller' => 'People', 'action' => 'photo_upload'], __('My Profile'));
			}
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload Document'), ['plugin' => false, 'controller' => 'People', 'action' => 'document_upload'], __('My Profile'));
			}
			if (Configure::read('App.urls.privacyPolicy')) {
				$this->_addMenuItem(__('Privacy Policy'), Configure::read('App.urls.privacyPolicy'), __('My Profile'));
			}
		}

		// Depending on the account type, and the available registrations, this may not be available
		// Admins and managers, anyone not logged in, and anyone with any registration history always get it
		if (Configure::read('feature.registration')) {
			// Parents always get the registration menu items
			if (!Configure::read('feature.minimal_menus') && ($this->Authorization->can(\App\Controller\PeopleController::class, 'show_registration'))) {
				$this->_addMenuItem(__('Registration'), ['plugin' => false, 'controller' => 'Events', 'action' => 'wizard']);
				$this->_addMenuItem(__('Wizard'), ['plugin' => false, 'controller' => 'Events', 'action' => 'wizard'], __('Registration'));
				$this->_addMenuItem(__('All Events'), ['plugin' => false, 'controller' => 'Events', 'action' => 'index'], __('Registration'));
				if ($identity && $identity->isLoggedIn() && !empty($this->UserCache->read('Registrations'))) {
					$this->_addMenuItem(__('My History'), ['plugin' => false, 'controller' => 'People', 'action' => 'registrations'], __('Registration'));
				}

				$can_pay = count($this->UserCache->read('RegistrationsCanPay'));
				if ($can_pay) {
					$this->_addMenuItem(__('Checkout {0}', "<span class=\"badge\">$can_pay</span>"), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'checkout'], __('Registration'));
				}
			}

			if ($identity && $identity->isManager()) {
				$this->_addMenuItem(__('Admin List'), ['plugin' => false, 'controller' => 'Events', 'action' => 'admin'], __('Registration'));
				$this->_addMenuItem(__('Create Event'), ['plugin' => false, 'controller' => 'Events', 'action' => 'add'], __('Registration'));
				$this->_addMenuItem(__('Unpaid'), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'unpaid'], __('Registration'));
				$this->_addMenuItem(__('Credits'), ['plugin' => false, 'controller' => 'Credits', 'action' => 'index'], __('Registration'));
				$this->_addMenuItem(__('Report'), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'report'], __('Registration'));

				$this->_addMenuItem(__('Statistics'), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'statistics'], __('Registration'));
				// TODOLATER $this->_addMenuItem(__('Accounting'), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'accounting'], __('Registration'));

				$this->_addMenuItem(__('Questionnaires'), ['plugin' => false, 'controller' => 'Questionnaires', 'action' => 'index'], __('Registration'));
				$this->_addMenuItem(__('Questions'), ['plugin' => false, 'controller' => 'Questions', 'action' => 'index'], [__('Registration'), __('Questionnaires')]);

				$this->_addMenuItem(__('Preregistrations'), ['plugin' => false, 'controller' => 'Preregistrations', 'action' => 'index'], __('Registration'));
				$this->_addMenuItem(__('Add'), ['plugin' => false, 'controller' => 'Preregistrations', 'action' => 'add'], [__('Registration'), __('Preregistrations')]);
			}
		}

		if ($identity && $identity->isLoggedIn()) {
			$this->_addMenuItem(__('Teams'), ['plugin' => false, 'controller' => 'Teams', 'action' => 'index']);
			$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Teams', 'action' => 'index'], __('Teams'));
			// If registrations are enabled, it takes care of team creation
			if (($identity && $identity->isManager()) || !Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Create Team'), ['plugin' => false, 'controller' => 'Teams', 'action' => 'add'], __('Teams'));
			}
			if (!Configure::read('feature.minimal_menus') && $identity && $identity->isManager()) {
				$this->loadModel('Teams');
				$new = $this->Teams->find()
					->where([
						'division_id IS' => null,
						'affiliate_id IN' => array_keys($affiliates),
					])
					->count();
				if ($new > 0) {
					$this->set('unassigned_teams', $new);
					$this->_addMenuItem(__('Unassigned Teams') . ' ' . "<span class=\"badge\">$new</span>", ['plugin' => false, 'controller' => 'Teams', 'action' => 'unassigned'], __('Teams'));
				}
				$this->_addMenuItem(__('Statistics'), ['plugin' => false, 'controller' => 'Teams', 'action' => 'statistics'], __('Teams'));
			}
		}

		if ($identity && $identity->isLoggedIn() && Configure::read('feature.franchises')) {
			$this->_addMenuItem(__('Franchises'), ['plugin' => false, 'controller' => 'Franchises', 'action' => 'index'], __('Teams'));
			$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Franchises', 'action' => 'index'], [__('Teams'), __('Franchises')]);
			$this->_addMenuItem(__('Create Franchise'), ['plugin' => false, 'controller' => 'Franchises', 'action' => 'add'], [__('Teams'), __('Franchises')]);
		}

		$this->_addMenuItem(__('Leagues'), ['plugin' => false, 'controller' => 'Leagues', 'action' => 'index']);
		$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Leagues', 'action' => 'index'], __('Leagues'));
		if ($identity && $identity->isManager()) {
			$this->_addMenuItem(__('League Summary'), ['plugin' => false, 'controller' => 'Leagues', 'action' => 'summary'], __('Leagues'));
			$this->_addMenuItem(__('Create League'), ['plugin' => false, 'controller' => 'Leagues', 'action' => 'add'], __('Leagues'));
		}

		$tournaments = Cache::remember('tournaments', function () {
			return TableRegistry::getTableLocator()->get('Leagues')->find('open')
				->matching('Divisions', function (Query $q) {
					return $q->where(['Divisions.schedule_type' => 'tournament']);
				})
				->order(['Leagues.open', 'Leagues.close', 'Leagues.id'])
				->combine('id', 'name')
				->toArray();
		}, 'today', I18n::getLocale());
		if (!empty($tournaments)) {
			$this->_addMenuItem(__('Tournaments'), ['plugin' => false, 'controller' => 'Tournaments', 'action' => 'index']);
			foreach ($tournaments as $id => $name) {
				// TODO: Handle custom URLs
				$this->_addMenuItem($name, ['plugin' => false, 'controller' => 'Tournaments', 'action' => 'view', 'tournament' => $id], __('Tournaments'));
			}
		}

		$this->_addMenuItem(Configure::read('UI.fields_cap'), ['plugin' => false, 'controller' => 'Facilities', 'action' => 'index']);
		$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Facilities', 'action' => 'index'], Configure::read('UI.fields_cap'));
		$this->_addMenuItem(__('Map of All {0}', Configure::read('UI.fields_cap')), ['plugin' => false, 'controller' => 'Maps', 'action' => 'index'], Configure::read('UI.fields_cap'), null, ['target' => 'map']);
		if ($identity && $identity->isManager()) {
			$this->_addMenuItem(__('Closed Facilities'), ['plugin' => false, 'controller' => 'Facilities', 'action' => 'closed'], Configure::read('UI.fields_cap'));
			$this->_addMenuItem(__('Create Facility'), ['plugin' => false, 'controller' => 'Facilities', 'action' => 'add'], Configure::read('UI.fields_cap'));

			if (!Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Add Bulk Game Slots'), ['plugin' => false, 'controller' => 'GameSlots', 'action' => 'add'], Configure::read('UI.fields_cap'));
			} else if (count($affiliates) == 1) {
				$this->_addMenuItem(__('Add Bulk Game Slots'), ['plugin' => false, 'controller' => 'GameSlots', 'action' => 'add', 'affiliate' => current(array_keys($affiliates))], Configure::read('UI.fields_cap'));
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__($name), ['plugin' => false, 'controller' => 'GameSlots', 'action' => 'add', 'affiliate' => $affiliate], [Configure::read('UI.fields_cap'), __('Add Bulk Game Slots')]);
				}
			}

			$this->_addMenuItem(__('Regions'), ['plugin' => false, 'controller' => 'Regions', 'action' => 'index'], Configure::read('UI.fields_cap'));
			$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Regions', 'action' => 'index'], [Configure::read('UI.fields_cap'), __('Regions')]);
			$this->_addMenuItem(__('Create Region'), ['plugin' => false, 'controller' => 'Regions', 'action' => 'add'], [Configure::read('UI.fields_cap'), __('Regions')]);

			$this->loadModel('People');
			if (!Configure::read('feature.minimal_menus')) {
				$new = $this->People->find()
					->distinct(['People.id'])
					->matching('Affiliates')
					->where([
						'People.status' => 'new',
						'People.complete' => true,
						'AffiliatesPeople.affiliate_id IN' => array_keys($affiliates),
					])
					->count();
				if ($new > 0) {
					$this->set('new_accounts', $new);
					$this->_addMenuItem(__('Approve New Accounts') . ' ' . "<span class=\"badge\">$new</span>", ['plugin' => false, 'controller' => 'People', 'action' => 'list_new'], __('People'));
				}

				if (Configure::read('feature.photos') && Configure::read('feature.approve_photos')) {
					$new = $this->People->Uploads->find()
						->where([
							'Uploads.approved' => false,
							'Uploads.type_id IS' => null,
						])
						->count();
					if ($new > 0) {
						$this->set('new_photos', $new);
						$this->_addMenuItem(__('Approve New Photos') . ' ' . "<span class=\"badge\">$new</span>", ['plugin' => false, 'controller' => 'People', 'action' => 'approve_photos'], __('People'));
					}
				}

				if (Configure::read('feature.documents')) {
					$new = $this->People->Uploads->find()
						->where([
							'Uploads.approved' => false,
							'Uploads.type_id IS NOT' => null,
						])
						->count();
					if ($new > 0) {
						$this->set('new_documents', $new);
						$this->_addMenuItem(__('Approve New Documents') . ' ' . "<span class=\"badge\">$new</span>", ['plugin' => false, 'controller' => 'People', 'action' => 'approve_documents'], __('People'));
					}
				}
			}

			// TODOLATER: $this->_addMenuItem(__('Bulk Import'), ['plugin' => false, 'controller' => 'Users', 'action' => 'import'], __('People'));
			if (Configure::read('feature.control_account_creation')) {
				$this->_addMenuItem(__('Create Account'), ['plugin' => false, 'controller' => 'Users', 'action' => 'create_account'], __('People'));
			}

			$this->_addMenuItem(__('List All'), ['plugin' => false, 'controller' => 'People', 'action' => 'index'], __('People'));
			$groups = $this->People->Groups->find()
				->enableHydration(false)
				->where([
					'OR' => [
						// We always want to include players, even if they aren't a valid "create account" group.
						'Groups.id' => GROUP_PLAYER,
						'Groups.active' => true,
					],
				])
				->order(['Groups.level', 'Groups.id'])
				->combine('id', 'name')
				->toArray();
			foreach ($groups as $group => $name) {
				$this->_addMenuItem(__(Inflector::pluralize($name)), ['plugin' => false, 'controller' => 'People', 'action' => 'index', 'group' => $group], [__('People'), __('List All')]);
			}
		}

		if ($identity && $identity->isLoggedIn()) {
			$this->_addMenuItem(__('Search'), ['plugin' => false, 'controller' => 'People', 'action' => 'search'], __('People'));
			if (Configure::read('feature.badges')) {
				$this->_addMenuItem(__('Badges'), ['plugin' => false, 'controller' => 'Badges', 'action' => 'index'], __('People'));
				$this->_addMenuItem(__('Nominate'), ['plugin' => false, 'controller' => 'People', 'action' => 'nominate'], [__('People'), __('Badges')]);
				if (!Configure::read('feature.minimal_menus') && $identity && $identity->isManager()) {
					$this->loadModel('People');
					$new = $this->People->Badges->find()
						->matching('People')
						->where([
							'BadgesPeople.approved' => false,
							'Badges.affiliate_id IN' => array_keys($affiliates),
						])
						->count();
					if ($new > 0) {
						$this->set('new_nominations', $new);
						$this->_addMenuItem(__('Approve Nominations') . ' ' . "<span class=\"badge\">$new</span>", ['plugin' => false, 'controller' => 'People', 'action' => 'approve_badges'], [__('People'), __('Badges')]);
					}
					$this->_addMenuItem(__('Deactivated'), ['plugin' => false, 'controller' => 'Badges', 'action' => 'deactivated'], [__('People'), __('Badges')]);
				}
			}
		}

		if ($identity && $identity->isManager()) {
			$this->_addMenuItem(__('By Name'), ['plugin' => false, 'controller' => 'People', 'action' => 'search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('By Email'), ['plugin' => false, 'controller' => 'People', 'action' => 'email_search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('By Rule'), ['plugin' => false, 'controller' => 'People', 'action' => 'rule_search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('By League'), ['plugin' => false, 'controller' => 'People', 'action' => 'league_search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('Inactive'), ['plugin' => false, 'controller' => 'People', 'action' => 'inactive_search'], [__('People'), __('Search')]);

			$this->_addMenuItem(__('Statistics'), ['plugin' => false, 'controller' => 'People', 'action' => 'statistics'], __('People'));
			if (Configure::read('profile.birthdate')) {
				$this->_addMenuItem(__('Demographics'), ['plugin' => false, 'controller' => 'People', 'action' => 'demographics'], __('People'));
			}
			$this->_addMenuItem(__('Participation'), ['plugin' => false, 'controller' => 'People', 'action' => 'participation'], [__('People'), __('Statistics')]);
			$this->_addMenuItem(__('Retention'), ['plugin' => false, 'controller' => 'People', 'action' => 'retention'], [__('People'), __('Statistics')]);

			$this->_addMenuItem(__('Newsletters'), ['plugin' => false, 'controller' => 'Newsletters', 'action' => 'index']);
			$this->_addMenuItem(__('Upcoming'), ['plugin' => false, 'controller' => 'Newsletters', 'action' => 'index'], __('Newsletters'));
			$this->_addMenuItem(__('Create Newsletter'), ['plugin' => false, 'controller' => 'Newsletters', 'action' => 'add'], __('Newsletters'));
			$this->_addMenuItem(__('All Newsletters'), ['plugin' => false, 'controller' => 'Newsletters', 'action' => 'past'], __('Newsletters'));
			$this->_addMenuItem(__('Mailing Lists'), ['plugin' => false, 'controller' => 'MailingLists', 'action' => 'index'], __('Newsletters'));
			$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'MailingLists', 'action' => 'index'], [__('Newsletters'), __('Mailing Lists')]);
			$this->_addMenuItem(__('Create Mailing List'), ['plugin' => false, 'controller' => 'MailingLists', 'action' => 'add'], [__('Newsletters'), __('Mailing Lists')]);
		}

		if ($identity && $identity->isAdmin()) {
			$this->_addMenuItem(__('Plugins'), ['plugin' => false, 'controller' => 'Plugins', 'action' => 'index'], __('Configuration'));

			if (Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Affiliates'), ['plugin' => false, 'controller' => 'Affiliates', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Permissions'), ['plugin' => false, 'controller' => 'Groups', 'action' => 'index'], __('Configuration'));
		}

		if ($identity && $identity->isManager()) {
			$this->_addMenuItem(__('Holidays'), ['plugin' => false, 'controller' => 'Holidays', 'action' => 'index'], __('Configuration'));
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload Types'), ['plugin' => false, 'controller' => 'Upload_types', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Waivers'), ['plugin' => false, 'controller' => 'Waivers', 'action' => 'index'], __('Configuration'));

			if (Configure::read('feature.contacts')) {
				$this->_addMenuItem(__('Contacts'), ['plugin' => false, 'controller' => 'Contacts', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Categories'), ['plugin' => false, 'controller' => 'Categories', 'action' => 'index'], __('Configuration'));
		}

		if ($this->Authorization->can(AllController::class, 'clear_cache')) {
			$this->_addMenuItem(__('Clear Cache'), ['plugin' => false, 'controller' => 'All', 'action' => 'clear_cache', 'return' => AppController::_return()], __('Configuration'));
		}

		if ($identity && $identity->isAdmin()) {
			$this->_addMenuItem(__('Organization'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'organization'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Features'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'feature'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Email'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'email'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Team'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'team'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('User'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user'], [__('Configuration'), __('Settings')]);
			// TODO: Let callbacks add themselves to menus as required, instead of hard-coding here and below.
			// This requires menu restructuring, with weights, etc.
			foreach (Configure::read('App.callbacks') as $name => $config) {
				if (is_numeric($name) && is_string($config)) {
					$name = $config;
				}
				$this->_addMenuItem(Inflector::humanize($name), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user_' . strtolower($name)], [__('Configuration'), __('Settings')]);
			}
			$this->_addMenuItem(__('Profile'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'profile'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Scoring'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'scoring'], [__('Configuration'), __('Settings')]);
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Registration'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'registration'], [__('Configuration'), __('Settings')]);
				if (Configure::read('registration.online_payments')) {
					$this->_addMenuItem(__('Payment'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'payment'], [__('Configuration'), __('Settings')]);
				}
			}
		}

		if (Configure::read('feature.affiliates') && $identity && $identity->isManager()) {
			if (count($affiliates) == 1 && !$identity->isAdmin()) {
				$affiliate = current(array_keys($affiliates));
				$this->_addMenuItem(__('Organization'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'organization', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Features'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'feature', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Email'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'email', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Team'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'team', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('User'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				foreach (Configure::read('App.callbacks') as $name => $config) {
					if (is_numeric($name) && is_string($config)) {
						$name = $config;
					}
					$this->_addMenuItem(Inflector::humanize($name), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user_' . strtolower($name), 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				}
				$this->_addMenuItem(__('Profile'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'profile', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Scoring'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'scoring', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				if (Configure::read('feature.registration')) {
					$this->_addMenuItem(__('Registration'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'registration', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
					if (Configure::read('registration.online_payments')) {
						$this->_addMenuItem(__('Payment'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'payment', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
					}
				}
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__('Organization'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'organization', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Features'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'feature', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Email'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'email', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Team'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'team', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('User'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					foreach (Configure::read('App.callbacks') as $name => $config) {
						if (is_numeric($name) && is_string($config)) {
							$name = $config;
						}
						$this->_addMenuItem(Inflector::humanize($name), ['plugin' => false, 'controller' => 'Settings', 'action' => 'user_' . strtolower($name), 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					}
					$this->_addMenuItem(__('Profile'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'profile', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Scoring'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'scoring', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					if (Configure::read('feature.registration')) {
						$this->_addMenuItem(__('Registration'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'registration', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
						if (Configure::read('registration.online_payments')) {
							$this->_addMenuItem(__('Payment'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'payment', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
						}
					}
				}
			}
		}

		if (Configure::read('feature.tasks')) {
			if ($identity && ($identity->isManager() || $identity->isOfficial() || $identity->isVolunteer())) {
				$this->_addMenuItem(__('Tasks'), ['plugin' => false, 'controller' => 'Tasks', 'action' => 'index']);
				$this->_addMenuItem(__('List'), ['plugin' => false, 'controller' => 'Tasks', 'action' => 'index'], __('Tasks'));
			}

			if ($identity && $identity->isManager()) {
				$this->_addMenuItem(__('Download All'), ['plugin' => false, 'controller' => 'Tasks', 'action' => 'index', '_ext' => 'csv'], __('Tasks'));
			}
		}

		if (!Configure::read('feature.minimal_menus') && $identity && $identity->isLoggedIn()) {
			$this->_initPersonalMenu();
			$relatives = $this->UserCache->allActAs(true, 'first_name');
			foreach ($relatives as $id => $name) {
				$this->_initPersonalMenu($id, $name);
			}
		}

		$this->_addMenuItem(__('Help'), ['plugin' => false, 'controller' => 'Help']);
		if (Configure::read('feature.contacts') && $this->UserCache->currentId()) {
			$this->_addMenuItem(__('Contact Us'), ['plugin' => false, 'controller' => 'Contacts', 'action' => 'message'], __('Help'));
		}
		$this->_addMenuItem(__('Help Index'), ['plugin' => false, 'controller' => 'Help'], __('Help'));
		$this->_addMenuItem(__('New Users'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'new_user'], __('Help'));
		$this->_addMenuItem(__('Advanced Users'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'advanced'], __('Help'));
		$this->_addMenuItem(__('Coaches/Captains'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'captain'], __('Help'));
		if (!Configure::read('feature.minimal_menus') && $identity && ($identity->isManager() || $identity->isCoordinator())) {
			$this->_addMenuItem(__('Coordinators'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'coordinator'], __('Help'));
		}
		if (ZULURU == 'Zuluru') {
			$this->_addMenuItem(__('Credits'), ['plugin' => false, 'controller' => 'All', 'action' => 'credits'], __('Help'));
		}

		if ($identity && $identity->isAdmin()) {
			$this->_addMenuItem(__('Site Setup and Configuration'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'administrator', 'setup'], [__('Help'), __('Administrators')]);
		}
		if ($identity && $identity->isManager()) {
			$this->_addMenuItem(__('Player Management'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'administrator', 'players'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('League Management'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'administrator', 'leagues'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('{0} Management', Configure::read('UI.field_cap')), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'administrator', 'fields'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('Registration'), ['plugin' => false, 'controller' => 'Help', 'action' => 'guide', 'administrator', 'registration'], [__('Help'), __('Administrators')]);
		}

		if (!$this->UserCache->currentId()) {
			$this->_addMenuItem(__('Create Account'), Configure::read('App.urls.register'));
		}

		if ($this->UserCache->currentId()) {
			if (Configure::read('App.urls.logout')) {
				$this->_addMenuItem(__('Logout'), Configure::read('App.urls.logout'));
			}
		} else {
			$this->_addMenuItem(__('Login'), Configure::read('App.urls.login'));
		}
	}

	/**
	 * Put personalized items like specific teams and divisions on the menu.
	 */
	protected function _initPersonalMenu($id = null, $name = null) {
		if ($id) {
			$this->_addMenuItem(__('View'), ['plugin' => false, 'controller' => 'People', 'action' => 'view', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Edit'), ['plugin' => false, 'controller' => 'People', 'action' => 'edit', 'act_as' => $id], [__('My Profile'), $name]);
			if (!$this->UserCache->read('Person.user_id', $id)) {
				$this->_addMenuItem(__('Create Login'), ['plugin' => false, 'controller' => 'People', 'action' => 'add_account', 'person' => $id], [__('My Profile'), $name]);
			}
			$this->_addMenuItem(__('Preferences'), ['plugin' => false, 'controller' => 'People', 'action' => 'preferences', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Waiver History'), ['plugin' => false, 'controller' => 'People', 'action' => 'waivers', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Upload Photo'), ['plugin' => false, 'controller' => 'People', 'action' => 'photo_upload', 'act_as' => $id], [__('My Profile'), $name]);

			if (Configure::read('feature.registration')) {
				if (!empty($this->UserCache->read('RegistrationsCanPay', $id))) {
					$this->_addMenuItem(__('Checkout'), ['plugin' => false, 'controller' => 'Registrations', 'action' => 'checkout', 'act_as' => $id], [__('Registration'), $name]);
				}

				if (!empty($this->UserCache->read('Registrations', $id))) {
					$this->_addMenuItem(__('History'), ['plugin' => false, 'controller' => 'People', 'action' => 'registrations', 'act_as' => $id], [__('Registration'), $name]);
				}
				$this->_addMenuItem(__('Wizard'), ['plugin' => false, 'controller' => 'Events', 'action' => 'wizard', 'act_as' => $id], [__('Registration'), $name]);
			}
		}

		$teams = $this->UserCache->read('Teams', $id);
		foreach ($teams as $team) {
			$this->_addTeamMenuItems($team, $id, $name);
		}
		if (!empty($this->UserCache->read('AllTeamIDs'))) {
			$this->_addMenuItem(__('My History'), ['plugin' => false, 'controller' => 'People', 'action' => 'teams'], __('Teams'));
		}

		if ($id) {
			if (!empty($this->UserCache->read('AllTeamIDs', $id))) {
				$this->_addMenuItem(__('History'), ['plugin' => false, 'controller' => 'People', 'action' => 'teams', 'person' => $id], [__('Teams'), $name]);
			}
		}

		if (!$id) {
			if (Configure::read('feature.franchises')) {
				$franchises = $this->UserCache->read('Franchises');
				if (!empty($franchises)) {
					foreach ($franchises as $franchise) {
						$this->_addFranchiseMenuItems($franchise);
					}
				}
			}

			$divisions = $this->UserCache->read('Divisions');
			foreach ($divisions as $division) {
				$this->_addDivisionMenuItems($division, $division->league);
			}
		}
	}

	/**
	 * Add all the links for a team to the menu.
	 */
	public function _addTeamMenuItems($team, $id = null, $name = null) {
		if ($id) {
			$path = [__('Teams'), $name];
		} else {
			$path = [__('Teams'), __('My Teams')];
		}

		$key = "{$team->name}::{$team->id}";

		if (!empty($team->division_id)) {
			$this->_addMenuItem($team->name . __(' ({0})', $team->division->long_league_name), ['plugin' => false, 'controller' => 'Teams', 'action' => 'view', 'team' => $team->id], $path, $key);
			$this->_addDivisionMenuItems($team->division, $team->division->league, $id, $name);
		} else {
			$this->_addMenuItem($team->name, ['plugin' => false, 'controller' => 'Teams', 'action' => 'view', 'team' => $team->id], $path, $key);
		}
	}

	/**
	 * Add all the links for a franchise to the menu.
	 */
	public function _addFranchiseMenuItems($franchise, $id = null, $name = null) {
		if ($id) {
			$path = [__('Teams'), __('Franchises'), $name];
		} else {
			$path = [__('Teams'), __('Franchises')];
		}

		$this->_addMenuItem($franchise->name, ['plugin' => false, 'controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $path, "{$franchise->name}::{$franchise->id}");
	}

	/**
	 * Add all the links for a division to the menu.
	 */
	public function _addDivisionMenuItems($division, $league, $id = null, $name = null) {
		if ($id) {
			$path = [__('Leagues'), $name];
		} else {
			$path = [__('Leagues'), __('My Leagues')];
		}

		$this->_addMenuItem($division->league_name, ['plugin' => false, 'controller' => 'Leagues', 'action' => 'view', 'league' => $league->id], $path);
	}

	/**
	 * Add a single item to the menu.
	 */
	public function _addMenuItem($name, $url = null, $path = [], $sort = null, $opts = null) {
		if ($sort === null)
			$sort = $name;
		if (!is_array($path))
			$path = [$path];
		$parent =& $this->menu_items;
		foreach ($path as $element) {
			if (!empty($element)) {
				if (!array_key_exists($element, $parent)) {
					$parent[$element] = ['items' => [], 'name' => $element];
				}
				$parent =& $parent[$element]['items'];
			}
		}

		if (!array_key_exists($sort, $parent)) {
			$parent[$sort] = ['items' => [], 'name' => $name];
		}

		if ($url) {
			$parent[$sort]['url'] = $url;
		}
		if ($opts) {
			$parent[$sort]['opts'] = $opts;
		}
	}

	/**
	 * Wrapper around the email component, simplifying sending the kinds of emails we want to send.
	 *
	 * @param mixed $opts Array of options controlling the email.
	 * @return mixed true if the email was sent, false otherwise.
	 */
	public static function _sendMail($opts) {
		$email = self::_getMailer();

		// Set up default values where applicable
		if (!array_key_exists('from', $opts)) {
			$email->setFrom([Configure::read('email.admin_email') => Configure::read('email.admin_name')]);
		}
		// TODO: Use $email->returnPath to set address for delivery failures

		// Prepare for building the list of locales to send the email in
		$locale = Configure::read('App.defaultLocale');
		$locales = [substr($locale, 0, 2) => $locale];

		// We may have been given complex Person arrays that the sender wants us to extract details from
		foreach (['to' => false, 'cc' => false, 'bcc' => false, 'from' => true, 'replyTo' => true] as $var => $single) {
			if (array_key_exists($var, $opts)) {
				$emails = self::_extractEmails($opts[$var], $single);
				if (!empty($emails)) {
					$email->$var($emails);
				}
				$locales = self::_extractLocales($opts[$var], $locales);
			}
		}

		// If there are no recipients, don't even bother trying to send
		if (empty($email->getTo())) {
			return (!empty($opts['ignore_empty_address']));
		}

		// Randomly order the list of locales, so there's no preference shown to anyone, and remember the default
		shuffle($locales);
		$email->setLocales($locales);

		// Set required fields
		$email->setEmailFormat($opts['sendAs'])->setSubject($opts['subject']);

		// Add any custom headers
		if (array_key_exists('header', $opts)) {
			$email->addHeaders($opts['header']);
		}

		// Add any view variables
		if (array_key_exists('viewVars', $opts)) {
			$email->setViewVars($opts['viewVars']);
		}

		// Check if there are attachments to be included
		$attachments = Configure::read('App.email.attachments');
		if (empty($attachments)) {
			$attachments = [];
		}
		if (!empty($opts['attachments'])) {
			$attachments = array_merge($attachments, $opts['attachments']);
		}
		if (!empty($attachments)) {
			$email->setAttachments($attachments);
		}

		$email->viewBuilder()->setHelpers([
			'Number', 'Text',
			'Html' => ['className' => 'ZuluruHtml'],
			'Time' => ['className' => 'ZuluruTime'],
		]);

		// Get ready and send it
		try {
			if (array_key_exists('content', $opts)) {
				$email->send($opts['content']);
			} else {
				$email->viewBuilder()->setTemplate($opts['template']);
				$email->send();
			}
		} catch (SocketException $ex) {
			Log::write(LogLevel::ERROR, 'Mail delivery error: ' . $ex->getMessage());
			return false;
		}

		return true;
	}

	public static function _getMailer() {
		$mailer = new Email(filter_var(env('DEBUG_EMAIL', false), FILTER_VALIDATE_BOOLEAN) ? 'debug' : 'default');

		// Set the theme, if any
		$theme = Configure::read('App.theme');
		if (!empty($theme)) {
			$mailer->viewBuilder()->setTheme($theme);
		}

		return $mailer;
	}

	public static function _extractEmails($input, $single = false, $check_relatives = true, $formatted = false) {
		$emails = [];

		if (is_a($input, 'Cake\ORM\Entity')) {
			// If it's an entity, extract what we can.
			if (!empty($input->full_name)) {
				$name = $input->full_name;
			} else if (!empty($input->name)) {
				$name = $input->name;
			}
			if (!empty($input->email)) {
				if (isset($name)) {
					$emails[trim($input->email)] = $name;
				} else {
					$emails[trim($input->email)] = $input->email;
				}
			}
			if (!empty($input->alternate_email)) {
				if (isset($name)) {
					$emails[trim($input->alternate_email)] = $name . __(' ({0})', __('alternate'));
				} else {
					$emails[trim($input->alternate_email)] = $input->alternate_email;
				}
			}

			// Check for relatives, if this is a person record without a user record
			if ($check_relatives && !empty($input->first_name) && empty($input->user_id)) {
				$relatives = UserCache::getInstance()->read('RelatedTo', $input->id);
				$emails = array_merge($emails, AppController::_extractEmails($relatives, false, false));
			}

			// If we haven't found anything yet, look further down the hierarchy
			if (empty($emails)) {
				foreach ($input as $values) {
					if (is_array($values)) {
						$emails = array_merge($emails, AppController::_extractEmails($values, false, $check_relatives));
					}
				}
			}
		} else if (is_array($input)) {
			// Any other array, assume it's a list of arrays or entities, and process each one
			foreach ($input as $key => $value) {
				if (is_numeric($key)) {
					$emails = array_merge($emails, AppController::_extractEmails($value, false, $check_relatives));
				} else {
					$emails[$key] = $value;
				}
			}
		} else if (is_string($input) && strpos($input, '@') !== false) {
			// Looks like an email address, most likely from newsletter sending
			$emails = [trim($input)];
		} else {
			// Anything else, we don't know what to do with!
			pr($input);
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		if ($formatted) {
			$emails = array_map(function ($email, $name) {
				if (!empty($name)) {
					return "$name <$email>";
				} else {
					return $email;
				}
			}, array_keys($emails), $emails);
		}

		if (!$single) {
			return $emails;
		}
		if (empty($emails)) {
			return null;
		}
		return array_slice($emails, 0, 1, true);
	}

	public static function _extractLocales($input, $locales) {
		if (is_a($input, 'App\Model\Entity\Person')) {
			// If it's a person, check if they have a language preference
			$preference = TableRegistry::getTableLocator()->get('Settings')->find()
				->where(['person_id' => $input->id, 'name' => 'language'])
				->first();
			if ($preference && $preference->value) {
				$locale = substr($preference->value, 0, 2);
				if (!array_key_exists($locale, $locales)) {
					$locales[$locale] = $preference->value;
				}
			}
		} else if (is_array($input)) {
			// Any other array, assume it's a list of entities, and process each one
			foreach ($input as $key => $value) {
				if (is_numeric($key)) {
					$locales = AppController::_extractLocales($value, $locales);
				}
			}
		}

		return $locales;
	}

	protected function _handlePersonSearch(array $url_params = [], array $conditions = []) {
		[$params, $url] = $this->_extractSearchParams($url_params);

		if (!empty($params)) {
			$names = [];
			foreach (['first_name', 'legal_name', 'last_name'] as $field) {
				if (!empty($params[$field])) {
					$names[] = trim($params[$field], ' *');
				}
			}
			$test = implode('', $names);
			$min = $this->Authentication->getIdentity()->isManager() ? 1 : 2;
			if (strlen($test) < $min) {
				$this->set('search_error', __('The search terms used are too general. Please be more specific.'));
			} else {
				// Set the default pagination order; query params may override it.
				// TODO: Multiple default sort fields break pagination links.
				// https://github.com/cakephp/cakephp/issues/7324 has related info.
				//$this->paginate['order'] = ['People.last_name', 'People.first_name', 'People.id'];
				$this->paginate['order'] = ['People.last_name'];

				$this->loadModel('People');
				$query = $this->People->find()
					->distinct(['People.id'])
					->contain(['Affiliates', 'Groups']);

				$columns = $this->People->schema()->columns();
				$search_conditions = [];
				foreach ($params as $field => $value) {
					if (!in_array($field, $columns)) {
						continue;
					}

					$term = "People.$field";
					if ($value) {
						if (strpos($value, '*') !== false) {
							$term .= ' LIKE';
							$value = strtr($value, '*', '%');
						}
						$search_conditions[] = [$term => $value];
					}
				}
				$query->where($search_conditions);

				// Match people in the affiliate, or admins who are effectively in all
				if (array_key_exists('affiliate_id', $params)) {
					$admins = $this->People->find()
						->enableHydration(false)
						->select(['People.id'])
						->matching('Groups', function (Query $q) {
							return $q->where(['Groups.id' => GROUP_ADMIN]);
						})
						->extract('id')
						->toArray();
					$query->matching('Affiliates')
						->andwhere(['OR' => [
							'AffiliatesPeople.affiliate_id' => $params['affiliate_id'],
							'People.id IN' => $admins,
						]])
						->order(['Affiliates.name']);
				}

				if (array_key_exists('group_id IN', $conditions)) {
					$query->matching('Groups', function (Query $q) use ($conditions) {
						return $q->where(['Groups.id IN' => $conditions['group_id IN']]);
					});
				}

				$this->set('people', $this->paginate($query));
			}
		}
		$this->set(compact('url'));
	}

	protected function _extractSearchParams(array $url_params = []) {
		if ($this->request->is('post')) {
			$params = $url = array_merge($this->request->getData(), $this->request->getQueryParams());
		} else {
			$params = $url = $this->request->getQueryParams();
		}
		foreach (['sort', 'direction', 'page'] as $pagination) {
			unset($params[$pagination]);
		}
		foreach ($url_params as $param) {
			unset($params[$param]);
		}
		unset($params['return']);
		if (!$this->Authorization->can(\App\Controller\PeopleController::class, 'display_legal_names')) {
			unset($params['legal_name']);
		}

		if (!$this->request->is('post')) {
			$this->setRequest($this->getRequest()->withParsedBody($params));
		}
		return [$params, $url];
	}

	public static function _isChild(Person $person) {
		// Assumption is that youth leagues will always require birthdates to properly categorize players
		if (empty($person->birthdate)) {
			return false;
		}

		// Special cases for children only apply to players
		if ($person->has('groups')) {
			$groups = $person->groups;
		} else if ($person->has('_matchingData') && array_key_exists('Groups', $person->_matchingData)) {
			$groups = $person->_matchingData['Groups'];
			if (!is_array($groups)) {
				$groups = [$groups];
			}
		} else {
			$groups = UserCache::getInstance()->read('Groups', $person->id);
		}
		if (!collection($groups)->some(function ($group) {
			return $group->id == GROUP_PLAYER;
		})) {
			return false;
		}

		if (Configure::read('feature.birth_year_only')) {
			$birth_year = $person->birthdate->year;
			if ($birth_year == 0) {
				return false;
			}
			return (FrozenTime::now()->year - $birth_year < 18);
		}
		return $person->birthdate->wasWithinLast('18 years');
	}

	/**
	 * Return a safely encoded version of the current URL that we can return to
	 * @return string
	 */
	public static function _return() {
		$request = Router::getRequest();
		if (!$request) {
			$url = '/';
		} else if ($request->is('ajax')) {
			$url = $request->referer(true);
		} else {
			$url = $request->getRequestTarget();
		}
		if (empty($url)) {
			$url = '/';
		}
		return \App\Lib\base64_url_encode($url);
	}

	// Wrapper around the Footprint function, to work with Authentication plugin
	protected function _setCurrentUser($user = null) {
		if (!$user) {
			$user = $this->request->getAttribute('identity');
			if ($user) {
				$user = $user->getOriginalData();
			}
		}

		return $this->_footprintSetCurrentUser($user);
	}

}
