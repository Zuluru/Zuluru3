<?php
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace App\Controller;

use App\Core\UserCache;
use App\Core\ModuleRegistry;
use App\Model\Entity\Person;
use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\I18n\Number;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Network\Exception\SocketException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Exception;
use Muffin\Footprint\Auth\FootprintAwareTrait;
use Psr\Log\LogLevel;

/**
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.controller
 * @property \Cake\Controller\Component\AuthComponent $Auth
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \App\Core\UserCache $UserCache
 * @property \App\Model\Table\ConfigurationTable $Configuration
 */
class AppController extends Controller {
	use FootprintAwareTrait;

	public $helpers = [
		'UserCache', 'Number', 'Text',
		'Html' => ['className' => 'ZuluruHtml'],
		'Form' => ['className' => 'ZuluruForm'],
		'Time' => ['className' => 'ZuluruTime'],
		'Game' => ['className' => 'ZuluruGame'],
		'BootstrapUI.Flash',
		'BootstrapUI.Paginator',
		'ZuluruBootstrap.Accordion',
		'ZuluruJquery.Jquery',
		//'Less.Less', // required for parsing less files
	];

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

		$this->loadComponent('Security');
		$this->loadComponent('Flash');
		$this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
		$this->loadComponent('Ajax.Ajax');

		// Don't attempt to do anything database- or user-related during installation
		if ($this->plugin == 'Installer') {
			return;
		}

		// Temporary code to remove cookies with a trailing slash in the path. These are
		// not sent by most browsers for equivalent paths that don't end in a slash. For
		// example, if the URL is /zuluru, a cookie for /zuluru/ won't be sent. But such
		// cookies *will* be sent for a URL like /zuluru/leagues; having cookies for both
		// /zuluru and /zuluru/ causes issues. The latter were incorrectly issued by early
		// versions of Zuluru 3, so here we remove them. See also CookiePathMiddleware.
		// This can go away in 2020, when all such cookies will be expired even if the
		// user never came back and hit this code in the meantime.
		$webroot = $this->request->getAttribute('webroot');
		if ($webroot != '/' && substr($webroot, -1) == '/') {
			foreach (['Auth', Configure::read('Session.cookie')] as $name) {
				if ($this->request->getCookieCollection()->get($name)) {
					$this->response = $this->response->withCookie($name, ['path' => $webroot, 'domain' => Configure::read('App.domain')]);
				}
			}
		}

		$this->UserCache = UserCache::getInstance(true);
		$this->moduleRegistry = new ModuleRegistry();

		// Use the configured model for handling hashing of passwords, and configure
		// the Auth field names using it
		$this->_userModel = Configure::read('Security.authModel');
		$users_table = TableRegistry::get($this->_userModel);
		if (!is_a($users_table, 'App\Model\Table\UsersTable')) {
			throw new Exception(__('Security.authModel is incorrectly configured!'));
		}

		if ($users_table->manageName == 'Zuluru') {
			// If Zuluru handles account management, handle old passwords and migrate them
			$hashMethod = Configure::read('Security.hashMethod') ?: $users_table->hashMethod;

			$hasher = [
				'className' => 'Fallback',
				'hashers' => [
					'Default',
					'Weak' => ['hashType' => $hashMethod],
					'WeakNoSalt' => ['hashType' => $hashMethod],
				],
			];
		} else {
			$hasher = [
				'className' => $users_table->manageName,
			];
		}

		if ($this->request->is('json')) {
			$this->loadComponent('Auth', [
				'storage' => 'Memory',
				'authenticate' => [
					'Form' => [
						'userModel' => $this->_userModel,
						'fields' => [
							'username' => $users_table->userField,
							'password' => $users_table->pwdField,
							'email' => $users_table->emailField,
						],
						'passwordHasher' => $hasher,
						'contain' => ['People'],
						'finder' => 'auth',
					],
					'ADmad/JwtAuth.Jwt' => [
						'userModel' => $this->_userModel,
						'fields' => [
							'username' => $users_table->primaryKey(),
							'password' => $users_table->pwdField,
						],

						'parameter' => 'token',

						// Boolean indicating whether the "sub" claim of JWT payload
						// should be used to query the Users model and get user info.
						// If set to `false` JWT's payload is directly returned.
						'queryDatasource' => true,
					]
				],

				'unauthorizedRedirect' => false,

				// If you don't have a login action in your application set
				// 'loginAction' to false to prevent getting a MissingRouteException.
				'loginAction' => false,
			]);
		} else {
			$this->loadComponent('Auth', [
				'authenticate' => [
					'Form' => [
						'userModel' => $this->_userModel,
						'fields' => [
							'username' => $users_table->userField,
							'password' => $users_table->pwdField,
							'email' => $users_table->emailField,
						],
						'passwordHasher' => $hasher,
						'contain' => ['People'],
						'finder' => 'auth',
					],
				],

				// By default, we tell people they need to log in. May be overridden later.
				'authError' => __('You must login to access full site functionality.'),

				// Set up various URLs to use
				'logoutAction' => Configure::read('App.urls.logout'),
				'loginRedirect' => '/',
				'logoutRedirect' => '/',
			]);
		}

		EventManager::instance()->on('Auth.afterIdentify', [$this, 'afterIdentify']);

		// Save a couple of bits of information from the selected auth model in the
		// configuration, so that it's accessible from anywhere instead of just places
		// that can access the model
		Configure::write('feature.external_accounts', $this->_userModel != 'Users');
		Configure::write('feature.manage_accounts', $users_table->manageAccounts);
		Configure::write('feature.manage_name', $users_table->manageName);

		// Set the default format for converting Time and Date objects to strings,
		// so that it matches the SQL format that we use for comparing.
		\Cake\I18n\FrozenTime::setToStringFormat('yyyy-MM-dd HH:mm:ss');
		\Cake\I18n\FrozenDate::setToStringFormat('yyyy-MM-dd');

		// If a third-party authentication session has expired, reset our own session
		// Also, if there's an ID in the session, but no matching profile; this will
		// happen when a logged-in user is merged or deleted, for example.
		// TODO: Delete all matching sessions when a user is merged or deleted
		$this->loadComponent('Login', ['className' => $users_table->loginComponent]);
		if ($this->Login->expired() || ($this->UserCache->currentId() && empty($this->UserCache->read('Person')))) {
			// If there is a redirect requested, logout will erase it, so remember it!
			if ($this->request->session()->check('Auth.redirect')) {
				$redirect = $this->request->session()->read('Auth.redirect');
			}

			$this->request->session()->delete('Zuluru');
			$this->Auth->logout();

			if (isset($redirect)) {
				$this->request->session()->write('Auth.redirect', $redirect);
			}
		}

		// Attempt a login through any third-party authentication
		// TODO: If we start to accept logins through Facebook and others, do we need to change this strategy?
		$this->Login->login($users_table);

		// Get the user information, if any, from the session
		$user = $this->Auth->user();

		// If the user has no session but does have "remember me" login information
		// saved in a cookie, try to log them in with that.
		if (!$user) {
			if ($this->request->is('json')) {
				$user = $this->Auth->identify();
			} else if ($this->request->getCookie('ZuluruAuth')) {
				$saved_data = $this->request->data;
				$this->request->data = $this->request->getCookie('ZuluruAuth');
				$user = $this->Auth->identify();
				$this->request->data = $saved_data;
			}
			if ($user) {
				$this->Auth->setUser($user);
			}
		}

		// TODO: Read these from site configuration
		if (!$this->request->session()->read('Zuluru.external_login')) {
			$this->Auth->config('loginAction', ['controller' => 'Users', 'action' => 'login']);
		} else {
			$this->Auth->config('loginAction', ['controller' => 'Leagues', 'action' => 'index']);
		}
		$this->Auth->config('unauthorizedRedirect', '/');
	}

	public function afterIdentify(CakeEvent $cakeEvent, $data, $auth) {
		$users_table = TableRegistry::get($this->_userModel);

		$this->UserCache->clear('Person', $data[$users_table->primaryKey()]);
		$this->UserCache->clear('User', $data[$users_table->primaryKey()]);

		$user = $users_table->get($data[$users_table->primaryKey()], [
			'contain' => ['People']
		]);
		$user->last_login = FrozenTime::now();
		$this->request->trustProxy = true;
		$user->client_ip = $this->request->clientIp();

		if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
			$user->password = $this->request->data('password');
		}

		// If there is no person record for this user, create it.
		if (!$user->has('person')) {
			$user->person = $users_table->People->createPersonRecord($user);
		}

		// Nothing useful to do if this save fails; they still log in, we just don't get an update of the IP and time
		$users_table->save($user, ['checkRules' => false]);
		$this->request->session()->write('Zuluru.zuluru_person_id', $user->person->id);
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

		EventManager::instance()->on('Flash', [$this, 'flash']);

		if (Configure::read('feature.items_per_page')) {
			$this->paginate = array_merge($this->paginate,
				['limit' => Configure::read('feature.items_per_page')]
			);
		}

		$this->_setPermissions();

		$this->request->addDetector('csv', ['param' => '_ext', 'value' => 'csv']);
		$this->response->type(['csv' => 'text/x-csv']);

		if ($this->request->is('ajax')) {
			// TODO: Find a better solution for black-holing of Ajax requests?
			$this->Security->config('unlockedActions', [$this->request->action]);
		}

		// Check if we need to redirect logged-in users for some required step first
		// We will allow them to see help or logout. Or get the leagues list, as that's where some things redirect to.
		$free = $this->_freeActions();
		if (Configure::read('Perm.is_logged_in') && !in_array($this->request->action, $free)) {
			if (($this->request->controller != 'People' || $this->request->action != 'edit') && $this->UserCache->read('Person.user_id') && !$this->request->is('json')) {
				if (empty($this->UserCache->read('Person.email'))) {
					$this->Flash->warning(__('Last time we tried to contact you, your email bounced. We require a valid email address as part of your profile. You must update it before proceeding.'));
					return $this->redirect(['controller' => 'People', 'action' => 'edit']);
				}
			}

			if (($this->request->controller != 'People' || $this->request->action != 'edit') && $this->UserCache->read('Person.complete') == 0 && !$this->request->is('json')) {
				$this->Flash->warning(__('Your profile is incomplete. You must update it before proceeding.'));
				return $this->redirect(['controller' => 'People', 'action' => 'edit']);
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
					$this->request->controller != 'Settings' &&
					// Allow people to change who they are acting as
					($this->request->controller != 'People' || $this->request->action != 'act_as') &&
					// We will let people look at information about teams that they've been invited to
					($this->request->controller != 'Teams' || !in_array($this->request->getQuery('team'), $response_required)) &&
					// Don't cause redirects for JSON requests
					!$this->request->is('json')
				) {
					$this->Flash->info(__('You have been invited to join a team, and must either accept or decline this invitation before proceeding. Before deciding, you have the ability to look at this team\'s roster, schedule, etc.'));
					return $this->redirect(['controller' => 'Teams', 'action' => 'view', 'team' => current($response_required)]);
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
		// TODOSECOND
		/*
		$this->_findLanguage();
		$i18n = I18n::getInstance();
		$this->request->session()->write('Config.language', $i18n->l10n->lang);
		Configure::write('Config.language', $i18n->l10n->lang);
		Configure::write('Config.language_name', $i18n->l10n->language);
		*/
		$lang = $this->request->session()->read('Config.language');
		if (!empty($lang)) {
			I18n::locale($lang);
		}
		Number::defaultCurrency(Configure::read('payment.currency'));
		// TODO: This causes duplication of values, e.g. options.skill
		//Configure::load('options');
		//Configure::load('sports');
	}

	protected function TODOSECOND__findLanguage() {
		$i18n = I18n::getInstance();

		$translations = Cache::read('available_translations');
		$translation_strings = Cache::read('available_translation_strings');
		if (!$translations || !$translation_strings) {
			$translations = ['en' => 'English'];
			$translation_strings = ["en: 'English'"];
			$dir = opendir(APP . 'Locale');
			if ($dir) {
				$map = $i18n->l10n->map();
				while (false !== ($entry = readdir($dir))) {
					if (array_key_exists($entry, $map) && file_exists(APP . 'locale' . DS . $entry . DS . DS . 'default.po')) {
						$code = $map[$entry];
						$catalog = $i18n->l10n->catalog();
						$translations[$code] = $catalog[$code]['language'];
						$translation_strings[] = "$code: '{$catalog[$code]['language']}'";
					}
				}
			}
			Cache::write('available_translations', $translations);
			Cache::write('available_translation_strings', $translation_strings);
		}
		Configure::write('available_translations', $translations);
		Configure::write('available_translation_strings', implode(', ', $translation_strings));

		$language = Configure::read('personal.language');
		if (!empty($language)) {
			$i18n->l10n->get($language);
			return;
		}

		if ($this->request->session()->check('Config.language')) {
			$i18n->l10n->get($this->request->session()->read('Config.language'));
			return;
		}

		$langs = [];

		// From http://www.thefutureoftheweb.com/blog/use-accept-language-header
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			// break up string into pieces (languages and q factors)
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

			if (count($lang_parse[1])) {
				// create a list like "en" => 0.8
				$langs = array_combine($lang_parse[1], $lang_parse[4]);

				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val) {
					if ($val === '') $langs[$lang] = 1;
				}

				// sort list based on value
				arsort($langs, SORT_NUMERIC);
			}
		}

		// See if we have a file that matches something the user wants
		foreach (array_keys($langs) as $lang) {
			$i18n->l10n->get($lang);
			foreach ($i18n->l10n->languagePath as $path) {
				if ($path == 'eng' || file_exists(APP . 'locale' . DS . strtolower(Text::slug($path)) . DS . 'default.po')) {
					return;
				}
			}
		}

		// Use the site's default language, if there is one
		if (Configure::read('site.default_language')) {
			$i18n->l10n->get(Configure::read('site.default_language'));
			return;
		}

		// Last ditch default to English
		$i18n->l10n->get('eng');
	}

	public function beforeRender(CakeEvent $cakeEvent) {
		parent::beforeRender($cakeEvent);

		if (isset($this->RequestHandler)) {
			$this->set('is_mobile', $this->RequestHandler->isMobile());
		} else {
			$this->set('is_mobile', false);
		}

		// Set view variables for the menu
		// TODO: Get the menu element name from some configuration. Probably a lot more to do to make it all non-Bootstrap-specific
		$this->set('menu_element', 'bootstrap');
		$this->set('menu_items', $this->menu_items);
	}

	/**
	 * Redirects to given $url, unless there is a "return" referer to go to instead.
	 * Script execution is halted after the redirect.
	 *
	 * @param string|array $url A string or array-based URL pointing to another location within the app,
	 *     or an absolute URL
	 * @param int $status HTTP status code (eg: 301)
	 * @return void|\Cake\Network\Response
	 * @link http://book.cakephp.org/3.0/en/controllers.html#Controller::redirect
	 */
	public function redirect($url, $status = 302) {
		if ($this->request->getQuery('return')) {
			// If there's a return requested, and nothing already saved to return to, remember the referrer
			$url = $this->decodeRedirect($this->request->getQuery('return'));
		} else if ($this->request->session()->check('Navigation.redirect')) {
			// If there's another page queued up, we go there
			$url = $this->request->session()->read('Navigation.redirect');
			$this->request->session()->delete('Navigation.redirect');
		}

		// String URLs might have come from $this->here, or might be '/'.
		// Either way, they need to be normalized.
		if (is_string($url)) {
			$url = Router::normalize($url);
		}

		return parent::redirect($url, $status);
	}

	/**
	 * Deletes any saved "return" referer and just redirects.
	 *
	 * @param string|array $url A string or array-based URL pointing to another location within the app,
	 *     or an absolute URL
	 * @return void|\Cake\Network\Response
	 */
	public function forceRedirect($url) {
		$this->request->session()->delete('Navigation.redirect');
		return parent::redirect($url);
	}

	/**
	 * Queue up a URL to redirect to after we're done the current thing.
	 *
	 * @param string|array $next URL to go to next after this redirect is done
	 */
	public function queueRedirect($next) {
		// TODO: We may at some point need to make this an actual queue instead of just one entry
		$this->request->session()->write('Navigation.redirect', $next);
	}

	private function decodeRedirect($url) {
		$url = \App\Lib\base64_url_decode($url);

		if (strpos($url, '?') !== false) {
			list($short_url, $querystr) = explode('?', $url);
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
			'active' => true,
		];
		$managed = $this->UserCache->read('ManagedAffiliateIDs');
		if (!empty($managed)) {
			$conditions['NOT'] = [
				'id IN' => $managed,
			];
		}

		$affiliates = $this->Affiliates->find('list', compact('conditions'))->toArray();

		$this->set(compact('affiliates'));
	}

	/**
	 * Basic check for authorization, based solely on the person's login group.
	 * Write some "is_" configuration for use elsewhere (is_admin, is_player, etc.).
	 *
	 * @access public
	 */
	protected function _setPermissions() {
		Configure::write(['Perm' => [
			'is_admin' => false,
			'is_manager' => false,
			'is_official' => false,
			'is_volunteer' => false,
			'is_coach' => false,
			'is_player' => false,
			'is_child' => false,
			'is_parent' => false,
			'is_logged_in' => false,
			'is_visitor' => true,
			'my_id' => $this->UserCache->currentId(),
		]]);

		$groups = $this->UserCache->read('Groups');
		$real_groups = $this->UserCache->read('Groups', $this->UserCache->realId());
		if (!empty($real_groups)) {
			$real_group_levels = collection($real_groups)->extract('level')->toArray();
		} else {
			$real_group_levels = [];
		}
		if ($this->UserCache->read('Person.status', $this->UserCache->realId()) == 'active') {
			// Approved accounts are granted permissions up to level 1,
			// since they can just add that group to themselves anyway.
			$real_group_levels[] = 1;
		}
		if (empty($real_group_levels)) {
			$max_level = 0;
		} else {
			$max_level = max($real_group_levels);
		}
		foreach ($groups as $group) {
			if ($this->UserCache->currentId() != $this->UserCache->realId()) {
				// Don't give people enhanced access just because the person they are acting as has it
				if ($group->level > $max_level) {
					continue;
				}
			}

			// TODO: Eliminate all these is_ variables and use database-driven permissions
			switch ($group->name) {
				case 'Administrator':
					Configure::write('Perm.is_admin', true);
					Configure::write('Perm.is_manager', true);
					break;

				case 'Manager':
					if (!empty($this->UserCache->read('ManagedAffiliateIDs'))) {
						Configure::write('Perm.is_manager', true);
					}
					break;

				case 'Official':
					Configure::write('Perm.is_official', true);
					break;

				case 'Volunteer':
					Configure::write('Perm.is_volunteer', true);
					break;

				case 'Coach':
					Configure::write('Perm.is_coach', true);
					break;

				case 'Player':
					Configure::write('Perm.is_player', true);
					break;

				case 'Parent':
					Configure::write('Perm.is_parent', true);
					break;
			}
		}
		if ($this->UserCache->currentId()) {
			// When acting as, be sure that the Footprint behaviour has the current user, not the real one
			$user = $this->UserCache->read('User');
			$this->_setCurrentUser($user);
			if ($this->_listener) {
				$this->_listener->setUser($user);
			}

			if ($this->UserCache->read('Person.status') != 'locked') {
				Configure::write('Perm.is_logged_in', true);
				Configure::write('Perm.is_visitor', false);
				Configure::write('Perm.is_child', $this->_isChild($this->UserCache->read('Person')));

				// Override default auth error message
				$this->Auth->config('authError', __('You do not have permission to access that page.'));
			} else {
				$this->Auth->config('authError', __('Your profile is currently {0}, so you can continue to use the site, but may be limited in some areas. To reactivate, {1}.',
					__($this->UserCache->read('Person.status')),
					__('contact {0}', Configure::read('email.admin_name'))
				));
			}
		}

		if (Configure::read('Perm.is_admin')) {
			// Admins have permission to do anything.
			$this->Auth->allow();
		} else {
			// Check what actions anyone (logged on or not) is allowed in this controller.
			$allowed = $this->request->is('json') ? $this->_publicJsonActions() : $this->_publicActions();

			// An empty array here means the controller has *no* public actions, but an empty
			// array passed to Auth->allow means *everything* is public.
			if (!empty($allowed)) {
				$this->Auth->allow($allowed);
			}
		}

		// Other authentication is handled through the isAuthorized function of
		// the individual controllers.
		$this->Auth->config('authorize', ['Controller']);
	}

	/**
	 * _publicActions method
	 *
	 * By default, nothing is public. Any controller with special permissions
	 * must override this function.
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		return [];
	}

	/**
	 * _publicJsonActions method
	 *
	 * By default, nothing is public. Any controller with special permissions
	 * must override this function.
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicJsonActions() {
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
	 * isAuthorized method
	 *
	 * By default, we allow the actions listed above (in the Auth->allow calls) and
	 * nothing else. Any controller with special permissions must override this function.
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		return false;
	}

	// Various ways to get the list of affiliates to show
	protected function _applicableAffiliates($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return [1 => Configure::read('organization.name')];
		}

		$this->loadModel('Affiliates');

		// If there's something in the URL, perhaps only use that
		$affiliate = $this->request->getQuery('affiliate');
		if ($affiliate === null) {
			// If the user has selected a specific affiliate to view, perhaps only use that
			$affiliate = $this->request->session()->read('Zuluru.CurrentAffiliate');
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || Configure::read('Perm.is_admin') ||
				(Configure::read('Perm.is_manager') && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs')))
			)
			{
				return $this->Affiliates->find()
					->hydrate(false)
					->where(['id' => $affiliate])
					->combine('id', 'name')
					->toArray();
			}
		}

		// Managers may get only their list of managed affiliates
		if (!Configure::read('Perm.is_admin') && Configure::read('Perm.is_manager') && $admin_only) {
			$affiliates = $this->UserCache->read('ManagedAffiliates');
			$affiliates = collection($affiliates)->combine('id', 'name')->toArray();
			ksort($affiliates);
			return $affiliates;
		}

		// Non-admins get their current list of "subscribed" affiliates
		if (Configure::read('Perm.is_logged_in') && !Configure::read('Perm.is_admin')) {
			$affiliates = $this->UserCache->read('Affiliates');
			if (!empty($affiliates)) {
				$affiliates = collection($affiliates)->combine('id', 'name')->toArray();
				ksort($affiliates);
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		return $this->Affiliates->find()
			->hydrate(false)
			->where(['active' => true])
			->order('name')
			->combine('id', 'name')
			->toArray();
	}

	// Various ways to get the list of affiliates to query
	public static function _applicableAffiliateIDs($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return [1];
		}

		// If there's something in the URL, perhaps only use that
		$request = Router::getRequest();
		if ($request) {
			$affiliate = $request->getQuery('affiliate');
			if ($affiliate === null) {
				// If the user has selected a specific affiliate to view, perhaps only use that
				$affiliate = $request->session()->read('Zuluru.CurrentAffiliate');
			}
		} else {
			$affiliate = null;
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || Configure::read('Perm.is_admin') ||
				(Configure::read('Perm.is_manager') && in_array($affiliate, UserCache::getInstance()->read('ManagedAffiliateIDs')))
			)
			{
				return [$affiliate];
			}
		}

		// Managers may get only their list of managed affiliates
		if (!Configure::read('Perm.is_admin') && Configure::read('Perm.is_manager') && $admin_only) {
			return UserCache::getInstance()->read('ManagedAffiliateIDs');
		}

		// Non-admins get their current list of selected affiliates
		if (Configure::read('Perm.is_logged_in') && !Configure::read('Perm.is_admin')) {
			$affiliates = UserCache::getInstance()->read('AffiliateIDs');
			if (!empty($affiliates)) {
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		return array_keys(TableRegistry::get('Affiliates')->find()
			->where(['active' => true])
			->combine('id', 'name')
			->toArray()
		);
	}

	/**
	 * Put basic items on the menu, some based on configuration settings.
	 * Other items like specific teams and divisions are added elsewhere.
	 */
	protected function _initMenu() {
		// Initialize the menu
		$this->menu_items = [];

		$groups = $this->UserCache->read('GroupIDs');
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$affiliates = $this->_applicableAffiliates(true);
		}

		if ($this->UserCache->currentId()) {
			$this->_addMenuItem(__('Dashboard'), ['controller' => 'All', 'action' => 'splash']);
		}

		if (Configure::read('Perm.is_logged_in')) {
			$this->_addMenuItem(__('My Profile'), ['controller' => 'People', 'action' => 'view']);
			$this->_addMenuItem(__('View'), ['controller' => 'People', 'action' => 'view'], __('My Profile'));
			$this->_addMenuItem(__('Edit'), ['controller' => 'People', 'action' => 'edit'], __('My Profile'));
			$this->_addMenuItem(__('Preferences'), ['controller' => 'People', 'action' => 'preferences'], __('My Profile'));
			if (in_array(GROUP_PARENT, $groups)) {
				$this->_addMenuItem(__('Add new child'), ['controller' => 'People', 'action' => 'add_relative'], __('My Profile'));
			}
			$this->_addMenuItem(__('Link to relative'), ['controller' => 'People', 'action' => 'link_relative'], __('My Profile'));
			if (in_array(GROUP_PLAYER, $groups)) {
				$this->_addMenuItem(__('Waiver history'), ['controller' => 'People', 'action' => 'waivers'], __('My Profile'));
			}
			if ($this->UserCache->read('Person.user_id')) {
				$this->_addMenuItem(__('Change password'), ['controller' => 'Users', 'action' => 'change_password'], __('My Profile'));
			}
			$status = $this->UserCache->read('Person.status');
			if ($status == 'active') {
				$this->_addMenuItem(__('Deactivate'), ['controller' => 'People', 'action' => 'deactivate'], __('My Profile'));
			} else if ($status == 'inactive') {
				$this->_addMenuItem(__('Reactivate'), ['controller' => 'People', 'action' => 'reactivate'], __('My Profile'));
			}
			if (Configure::read('feature.photos')) {
				$this->_addMenuItem(__('Upload photo'), ['controller' => 'People', 'action' => 'photo_upload'], __('My Profile'));
			}
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload document'), ['controller' => 'People', 'action' => 'document_upload'], __('My Profile'));
			}
			if (Configure::read('App.urls.privacyPolicy')) {
				$this->_addMenuItem(__('Privacy policy'), Configure::read('App.urls.privacyPolicy'), __('My Profile'));
			}
		}

		// Depending on the account type, and the available registrations, this may not be available
		// Admins and managers, anyone not logged in, and anyone with any registration history always get it
		if (Configure::read('feature.registration')) {
			// Parents always get the registration menu items
			if (!Configure::read('feature.minimal_menus') && (in_array(GROUP_PARENT, $groups) || $this->_showRegistration(null, $groups))) {
				$this->_addMenuItem(__('Registration'), ['controller' => 'Events', 'action' => 'wizard']);
				$this->_addMenuItem(__('Wizard'), ['controller' => 'Events', 'action' => 'wizard'], __('Registration'));
				$this->_addMenuItem(__('All events'), ['controller' => 'Events', 'action' => 'index'], __('Registration'));
				if (Configure::read('Perm.is_logged_in') && !empty($this->UserCache->read('Registrations'))) {
					$this->_addMenuItem(__('My history'), ['controller' => 'People', 'action' => 'registrations'], __('Registration'));
				}

				$can_pay = count($this->UserCache->read('RegistrationsCanPay'));
				if ($can_pay) {
					$this->_addMenuItem(__('Checkout {0}', "<span class=\"badge\">$can_pay</span>"), ['controller' => 'Registrations', 'action' => 'checkout'], __('Registration'));
				}
			}

			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
				$this->_addMenuItem(__('Create event'), ['controller' => 'Events', 'action' => 'add'], __('Registration'));
				$this->_addMenuItem(__('Unpaid'), ['controller' => 'Registrations', 'action' => 'unpaid'], __('Registration'));
				$this->_addMenuItem(__('Credits'), ['controller' => 'Registrations', 'action' => 'credits'], __('Registration'));
				$this->_addMenuItem(__('Report'), ['controller' => 'Registrations', 'action' => 'report'], __('Registration'));

				$this->_addMenuItem(__('Statistics'), ['controller' => 'Registrations', 'action' => 'statistics'], __('Registration'));
				// TODOLATER $this->_addMenuItem(__('Accounting'), ['controller' => 'Registrations', 'action' => 'accounting'], __('Registration'));

				$this->_addMenuItem(__('Questionnaires'), ['controller' => 'Questionnaires', 'action' => 'index'], __('Registration'));
				$this->_addMenuItem(__('Questions'), ['controller' => 'Questions', 'action' => 'index'], [__('Registration'), __('Questionnaires')]);

				$this->_addMenuItem(__('Preregistrations'), ['controller' => 'Preregistrations', 'action' => 'index'], __('Registration'));
				$this->_addMenuItem(__('Add'), ['controller' => 'Preregistrations', 'action' => 'add'], [__('Registration'), __('Preregistrations')]);
			}
		}

		if (Configure::read('Perm.is_logged_in')) {
			$this->_addMenuItem(__('Teams'), ['controller' => 'Teams', 'action' => 'index']);
			$this->_addMenuItem(__('List'), ['controller' => 'Teams', 'action' => 'index'], __('Teams'));
			// If registrations are enabled, it takes care of team creation
			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || !Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Create team'), ['controller' => 'Teams', 'action' => 'add'], __('Teams'));
			}
			if (!Configure::read('feature.minimal_menus') && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
				$this->loadModel('Teams');
				$new = $this->Teams->find()
					->where([
						'division_id IS' => null,
						'affiliate_id IN' => array_keys($affiliates),
					])
					->count();
				if ($new > 0) {
					$this->set('unassigned_teams', $new);
					$this->_addMenuItem(__('Unassigned teams {0}', "<span class=\"badge\">$new</span>"), ['controller' => 'Teams', 'action' => 'unassigned'], __('Teams'));
				}
				$this->_addMenuItem(__('Statistics'), ['controller' => 'Teams', 'action' => 'statistics'], __('Teams'));
			}
		}

		if (Configure::read('Perm.is_logged_in') && Configure::read('feature.franchises')) {
			$this->_addMenuItem(__('Franchises'), ['controller' => 'Franchises', 'action' => 'index'], __('Teams'));
			$this->_addMenuItem(__('List'), ['controller' => 'Franchises', 'action' => 'index'], [__('Teams'), __('Franchises')]);
			$this->_addMenuItem(__('Create franchise'), ['controller' => 'Franchises', 'action' => 'add'], [__('Teams'), __('Franchises')]);
		}

		$this->_addMenuItem(__('Leagues'), ['controller' => 'Leagues', 'action' => 'index']);
		$this->_addMenuItem(__('List'), ['controller' => 'Leagues', 'action' => 'index'], __('Leagues'));
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$this->_addMenuItem(__('League summary'), ['controller' => 'Leagues', 'action' => 'summary'], __('Leagues'));
			$this->_addMenuItem(__('Create league'), ['controller' => 'Leagues', 'action' => 'add'], __('Leagues'));
		}

		$tournaments = Cache::remember('tournaments', function () {
			return TableRegistry::get('Leagues')->find('open')
				->matching('Divisions', function (Query $q) {
					return $q->where(['Divisions.schedule_type' => 'tournament']);
				})
				->order(['Leagues.open', 'Leagues.close', 'Leagues.id'])
				->combine('id', 'name')
				->toArray();
		}, 'today');
		if (!empty($tournaments)) {
			$this->_addMenuItem(__('Tournaments'), ['controller' => 'Tournaments', 'action' => 'index']);
			foreach ($tournaments as $id => $name) {
				// TODO: Handle custom URLs
				$this->_addMenuItem($name, ['controller' => 'Tournaments', 'action' => 'view', 'tournament' => $id], __('Tournaments'));
			}
		}

		$this->_addMenuItem(__(Configure::read('UI.fields_cap')), ['controller' => 'Facilities', 'action' => 'index']);
		$this->_addMenuItem(__('List'), ['controller' => 'Facilities', 'action' => 'index'], __(Configure::read('UI.fields_cap')));
		$this->_addMenuItem(__('Map of all {0}', __(Configure::read('UI.fields'))), ['controller' => 'Maps', 'action' => 'index'], __(Configure::read('UI.fields_cap')), null, ['target' => 'map']);
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$this->_addMenuItem(__('Closed facilities'), ['controller' => 'Facilities', 'action' => 'closed'], __(Configure::read('UI.fields_cap')));
			$this->_addMenuItem(__('Create facility'), ['controller' => 'Facilities', 'action' => 'add'], __(Configure::read('UI.fields_cap')));

			if (!Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Add bulk gameslots'), ['controller' => 'GameSlots', 'action' => 'add'], __(Configure::read('UI.fields_cap')));
			} else if (count($affiliates) == 1) {
				$this->_addMenuItem(__('Add bulk gameslots'), ['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => current(array_keys($affiliates))], __(Configure::read('UI.fields_cap')));
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__($name), ['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => $affiliate], [__(Configure::read('UI.fields_cap')), __('Add bulk gameslots')]);
				}
			}

			$this->_addMenuItem(__('Regions'), ['controller' => 'Regions', 'action' => 'index'], __(Configure::read('UI.fields_cap')));
			$this->_addMenuItem(__('List'), ['controller' => 'Regions', 'action' => 'index'], [__(Configure::read('UI.fields_cap')), __('Regions')]);
			$this->_addMenuItem(__('Create region'), ['controller' => 'Regions', 'action' => 'add'], [__(Configure::read('UI.fields_cap')), __('Regions')]);

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
					$this->_addMenuItem(__('Approve new accounts {0}', "<span class=\"badge\">$new</span>"), ['controller' => 'People', 'action' => 'list_new'], __('People'));
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
						$this->_addMenuItem(__('Approve new photos {0}', "<span class=\"badge\">$new</span>"), ['controller' => 'People', 'action' => 'approve_photos'], __('People'));
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
						$this->_addMenuItem(__('Approve new documents {0}', "<span class=\"badge\">$new</span>"), ['controller' => 'People', 'action' => 'approve_documents'], __('People'));
					}
				}
			}

			$this->_addMenuItem(__('Bulk import'), ['controller' => 'Users', 'action' => 'import'], __('People'));
			if (Configure::read('feature.manage_accounts')) {
				$this->_addMenuItem(__('Create account'), ['controller' => 'Users', 'action' => 'create_account'], __('People'));
			}

			$this->_addMenuItem(__('List all'), ['controller' => 'People', 'action' => 'index'], __('People'));
			$groups = $this->People->Groups->find()
				->hydrate(false)
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
				$this->_addMenuItem(__(Inflector::pluralize($name)), ['controller' => 'People', 'action' => 'index', 'group' => $group], [__('People'), __('List all')]);
			}
		}

		if (Configure::read('Perm.is_logged_in')) {
			$this->_addMenuItem(__('Search'), ['controller' => 'People', 'action' => 'search'], __('People'));
			if (Configure::read('feature.badges')) {
				$this->_addMenuItem(__('Badges'), ['controller' => 'Badges', 'action' => 'index'], __('People'));
				$this->_addMenuItem(__('Nominate'), ['controller' => 'People', 'action' => 'nominate'], [__('People'), __('Badges')]);
				if (!Configure::read('feature.minimal_menus') && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
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
						$this->_addMenuItem(__('Approve nominations {0}', "<span class=\"badge\">$new</span>"), ['controller' => 'People', 'action' => 'approve_badges'], [__('People'), __('Badges')]);
					}
					$this->_addMenuItem(__('Deactivated'), ['controller' => 'Badges', 'action' => 'deactivated'], [__('People'), __('Badges')]);
				}
			}
		}

		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$this->_addMenuItem(__('By name'), ['controller' => 'People', 'action' => 'search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('By rule'), ['controller' => 'People', 'action' => 'rule_search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('By league'), ['controller' => 'People', 'action' => 'league_search'], [__('People'), __('Search')]);
			$this->_addMenuItem(__('Inactive'), ['controller' => 'People', 'action' => 'inactive_search'], [__('People'), __('Search')]);

			$this->_addMenuItem(__('Statistics'), ['controller' => 'People', 'action' => 'statistics'], __('People'));
			$this->_addMenuItem(__('Participation'), ['controller' => 'People', 'action' => 'participation'], [__('People'), __('Statistics')]);
			$this->_addMenuItem(__('Retention'), ['controller' => 'People', 'action' => 'retention'], [__('People'), __('Statistics')]);

			$this->_addMenuItem(__('Newsletters'), ['controller' => 'Newsletters', 'action' => 'index']);
			$this->_addMenuItem(__('Upcoming'), ['controller' => 'Newsletters', 'action' => 'index'], __('Newsletters'));
			$this->_addMenuItem(__('Create newsletter'), ['controller' => 'Newsletters', 'action' => 'add'], __('Newsletters'));
			$this->_addMenuItem(__('All newsletters'), ['controller' => 'Newsletters', 'action' => 'past'], __('Newsletters'));
			$this->_addMenuItem(__('Mailing Lists'), ['controller' => 'MailingLists', 'action' => 'index'], __('Newsletters'));
			$this->_addMenuItem(__('List'), ['controller' => 'MailingLists', 'action' => 'index'], [__('Newsletters'), __('Mailing Lists')]);
			$this->_addMenuItem(__('Create mailing list'), ['controller' => 'MailingLists', 'action' => 'add'], [__('Newsletters'), __('Mailing Lists')]);
		}

		if (Configure::read('Perm.is_admin')) {
			if (Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Affiliates'), ['controller' => 'Affiliates', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Permissions'), ['controller' => 'Groups', 'action' => 'index'], __('Configuration'));
		}

		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$this->_addMenuItem(__('Holidays'), ['controller' => 'Holidays', 'action' => 'index'], __('Configuration'));
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload Types'), ['controller' => 'Upload_types', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Waivers'), ['controller' => 'Waivers', 'action' => 'index'], __('Configuration'));

			if (Configure::read('feature.contacts')) {
				$this->_addMenuItem(__('Contacts'), ['controller' => 'Contacts', 'action' => 'index'], __('Configuration'));
			}

			$this->_addMenuItem(__('Clear cache'), ['controller' => 'All', 'action' => 'clear_cache', 'return' => AppController::_return()], __('Configuration'));
		}

		if (Configure::read('Perm.is_admin')) {
			$this->_addMenuItem(__('Organization'), ['controller' => 'Settings', 'action' => 'organization'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Features'), ['controller' => 'Settings', 'action' => 'feature'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Email'), ['controller' => 'Settings', 'action' => 'email'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Team'), ['controller' => 'Settings', 'action' => 'team'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('User'), ['controller' => 'Settings', 'action' => 'user'], [__('Configuration'), __('Settings')]);
			// TODO: Let callbacks add themselves to menus as required, instead of hard-coding here and below.
			// This requires menu restructuring, with weights, etc.
			foreach (Configure::read('App.callbacks') as $name => $config) {
				if (is_numeric($name) && is_string($config)) {
					$name = $config;
				}
				$this->_addMenuItem(Inflector::humanize($name), ['controller' => 'Settings', 'action' => 'user_' . strtolower($name)], [__('Configuration'), __('Settings')]);
			}
			$this->_addMenuItem(__('Profile'), ['controller' => 'Settings', 'action' => 'profile'], [__('Configuration'), __('Settings')]);
			$this->_addMenuItem(__('Scoring'), ['controller' => 'Settings', 'action' => 'scoring'], [__('Configuration'), __('Settings')]);
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Registration'), ['controller' => 'Settings', 'action' => 'registration'], [__('Configuration'), __('Settings')]);
				if (Configure::read('registration.online_payments')) {
					$this->_addMenuItem(__('Payment'), ['controller' => 'Settings', 'action' => 'payment'], [__('Configuration'), __('Settings')]);
				}
			}
		}

		if (Configure::read('feature.affiliates') && Configure::read('Perm.is_manager')) {
			if (count($affiliates) == 1 && !Configure::read('Perm.is_admin')) {
				$affiliate = current(array_keys($affiliates));
				$this->_addMenuItem(__('Organization'), ['controller' => 'Settings', 'action' => 'organization', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Features'), ['controller' => 'Settings', 'action' => 'feature', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Email'), ['controller' => 'Settings', 'action' => 'email', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Team'), ['controller' => 'Settings', 'action' => 'team', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('User'), ['controller' => 'Settings', 'action' => 'user', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				foreach (Configure::read('App.callbacks') as $name => $config) {
					if (is_numeric($name) && is_string($config)) {
						$name = $config;
					}
					$this->_addMenuItem(Inflector::humanize($name), ['controller' => 'Settings', 'action' => 'user_' . strtolower($name), 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				}
				$this->_addMenuItem(__('Profile'), ['controller' => 'Settings', 'action' => 'profile', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				$this->_addMenuItem(__('Scoring'), ['controller' => 'Settings', 'action' => 'scoring', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
				if (Configure::read('feature.registration')) {
					$this->_addMenuItem(__('Registration'), ['controller' => 'Settings', 'action' => 'registration', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
					if (Configure::read('registration.online_payments')) {
						$this->_addMenuItem(__('Payment'), ['controller' => 'Settings', 'action' => 'payment', 'affiliate' => $affiliate], [__('Configuration'), __('Settings')]);
					}
				}
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__('Organization'), ['controller' => 'Settings', 'action' => 'organization', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Features'), ['controller' => 'Settings', 'action' => 'feature', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Email'), ['controller' => 'Settings', 'action' => 'email', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Team'), ['controller' => 'Settings', 'action' => 'team', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('User'), ['controller' => 'Settings', 'action' => 'user', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					foreach (Configure::read('App.callbacks') as $name => $config) {
						if (is_numeric($name) && is_string($config)) {
							$name = $config;
						}
						$this->_addMenuItem(Inflector::humanize($name), ['controller' => 'Settings', 'action' => 'user_' . strtolower($name), 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					}
					$this->_addMenuItem(__('Profile'), ['controller' => 'Settings', 'action' => 'profile', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					$this->_addMenuItem(__('Scoring'), ['controller' => 'Settings', 'action' => 'scoring', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
					if (Configure::read('feature.registration')) {
						$this->_addMenuItem(__('Registration'), ['controller' => 'Settings', 'action' => 'registration', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
						if (Configure::read('registration.online_payments')) {
							$this->_addMenuItem(__('Payment'), ['controller' => 'Settings', 'action' => 'payment', 'affiliate' => $affiliate], [__('Configuration'), __('Settings'), __($name)]);
						}
					}
				}
			}
		}

		if (Configure::read('feature.tasks')) {
			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || Configure::read('Perm.is_official') || Configure::read('Perm.is_volunteer')) {
				$this->_addMenuItem(__('Tasks'), ['controller' => 'Tasks', 'action' => 'index']);
				$this->_addMenuItem(__('List'), ['controller' => 'Tasks', 'action' => 'index'], __('Tasks'));
			}

			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
				$this->_addMenuItem(__('Categories'), ['controller' => 'Categories', 'action' => 'index'], __('Tasks'));
				$this->_addMenuItem(__('Download All'), ['controller' => 'Tasks', 'action' => 'index', '_ext' => 'csv'], __('Tasks'));
			}
		}

		if (!Configure::read('feature.minimal_menus') && Configure::read('Perm.is_logged_in')) {
			$this->_initPersonalMenu();
			$relatives = $this->UserCache->allActAs(true, 'first_name');
			foreach ($relatives as $id => $name) {
				$this->_initPersonalMenu($id, $name);
			}
		}

		$this->_addMenuItem(__('Help'), ['controller' => 'Help']);
		if (Configure::read('feature.contacts') && $this->UserCache->currentId()) {
			$this->_addMenuItem(__('Contact us'), ['controller' => 'Contacts', 'action' => 'message'], __('Help'));
		}
		$this->_addMenuItem(__('Help index'), ['controller' => 'Help'], __('Help'));
		$this->_addMenuItem(__('New users'), ['controller' => 'Help', 'action' => 'guide', 'new_user'], __('Help'));
		$this->_addMenuItem(__('Advanced users'), ['controller' => 'Help', 'action' => 'guide', 'advanced'], __('Help'));
		$this->_addMenuItem(__('Coaches/Captains'), ['controller' => 'Help', 'action' => 'guide', 'captain'], __('Help'));
		if (!Configure::read('feature.minimal_menus') && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $this->UserCache->read('DivisionIDs'))) {
			$this->_addMenuItem(__('Coordinators'), ['controller' => 'Help', 'action' => 'guide', 'coordinator'], __('Help'));
		}
		if (ZULURU == 'Zuluru') {
			$this->_addMenuItem(__('Credits'), ['controller' => 'All', 'action' => 'credits'], __('Help'));
		}

		if (Configure::read('Perm.is_admin')) {
			$this->_addMenuItem(__('Site setup and configuration'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'setup'], [__('Help'), __('Administrators')]);
		}
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$this->_addMenuItem(__('Player management'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'players'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('League management'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'leagues'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('{0} management', __(Configure::read('UI.field_cap'))), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'fields'], [__('Help'), __('Administrators')]);
			$this->_addMenuItem(__('Registration'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'registration'], [__('Help'), __('Administrators')]);
		}

		if (!$this->UserCache->currentId()) {
			$this->_addMenuItem(__('Create account'), Configure::read('App.urls.register'));
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
			$this->_addMenuItem(__('View'), ['controller' => 'People', 'action' => 'view', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Edit'), ['controller' => 'People', 'action' => 'edit', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Preferences'), ['controller' => 'People', 'action' => 'preferences', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Waiver history'), ['controller' => 'People', 'action' => 'waivers', 'act_as' => $id], [__('My Profile'), $name]);
			$this->_addMenuItem(__('Upload photo'), ['controller' => 'People', 'action' => 'photo_upload', 'act_as' => $id], [__('My Profile'), $name]);

			if (Configure::read('feature.registration')) {
				if (!empty($this->UserCache->read('RegistrationsCanPay', $id))) {
					$this->_addMenuItem(__('Checkout'), ['controller' => 'Registrations', 'action' => 'checkout', 'act_as' => $id], [__('Registration'), $name]);
				}

				if (!empty($this->UserCache->read('Registrations', $id))) {
					$this->_addMenuItem(__('History'), ['controller' => 'People', 'action' => 'registrations', 'act_as' => $id], [__('Registration'), $name]);
				}
				$this->_addMenuItem(__('Wizard'), ['controller' => 'Events', 'action' => 'wizard', 'act_as' => $id], [__('Registration'), $name]);
			}
		}

		$teams = $this->UserCache->read('Teams', $id);
		foreach ($teams as $team) {
			$this->_addTeamMenuItems($team, $id, $name);
		}
		if (!empty($this->UserCache->read('AllTeamIDs'))) {
			$this->_addMenuItem(__('My history'), ['controller' => 'People', 'action' => 'teams'], __('Teams'));
		}

		if ($id) {
			if (!empty($this->UserCache->read('AllTeamIDs', $id))) {
				$this->_addMenuItem(__('History'), ['controller' => 'People', 'action' => 'teams', 'person' => $id], [__('Teams'), $name]);
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
			$this->_addMenuItem($team->name . ' (' . $team->division->long_league_name . ')', ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], $path, $key);
			$this->_addDivisionMenuItems($team->division, $team->division->league, $id, $name);
		} else {
			$this->_addMenuItem($team->name, ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], $path, $key);
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

		$this->_addMenuItem($franchise->name, ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $path, "{$franchise->name}::{$franchise->id}");
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

		if (empty($division->league_name)) {
			trigger_error('TODOTESTING: Read division data with hydration so league names are present', E_USER_ERROR);
			exit;
		}

		$this->_addMenuItem($division->league_name, ['controller' => 'Leagues', 'action' => 'view', 'league' => $league->id], $path);
	}

	public static function _showRegistration($id, $groups = null) {
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || !Configure::read('Perm.is_logged_in') || !empty(UserCache::getInstance()->read('Registrations', $id))) {
			return true;
		}

		// Players always get it
		if ($groups === null) {
			$groups = UserCache::getInstance()->read('GroupIDs', $id);
		}
		if (in_array(GROUP_PLAYER, $groups)) {
			return true;
		}

		// If there are any generic events available, everyone gets it
		$affiliates = self::_applicableAffiliateIDs();
		$events_table = TableRegistry::get('Events');
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
		if (in_array(GROUP_COACH, $groups)) {
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
			$email->from([Configure::read('email.admin_email') => Configure::read('email.admin_name')]);
		}
		// TODO: Use $email->returnPath to set address for delivery failures

		// We may have been given complex Person arrays that the sender wants us to extract details from
		foreach (['to' => false, 'cc' => false, 'bcc' => false, 'from' => true, 'replyTo' => true] as $var => $single) {
			if (array_key_exists($var, $opts)) {
				$emails = self::_extractEmails($opts[$var], $single);
				if (!empty($emails)) {
					$email->$var($emails);
				}
			}
		}

		// If there are no recipients, don't even bother trying to send
		if (empty($email->to())) {
			return (!empty($opts['ignore_empty_address']));
		}

		// Set required fields
		$email->emailFormat($opts['sendAs'])->subject($opts['subject']);

		// Add any custom headers
		if (array_key_exists('header', $opts)) {
			$email->addHeaders($opts['header']);
		}

		// Add any view variables
		if (array_key_exists('viewVars', $opts)) {
			$email->viewVars($opts['viewVars']);
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
			$email->attachments($attachments);
		}

		$email->helpers([
			'Number', 'Text',
			'Html' => ['className' => 'ZuluruHtml'],
			'Time' => ['className' => 'ZuluruTime'],
		]);

		// Get ready and send it
		try {
			if (array_key_exists('content', $opts)) {
				$email->send($opts['content']);
			} else {
				$email->template($opts['template']);
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
			$mailer->theme($theme);
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
					$emails[$input->email] = $name;
				} else {
					$emails[$input->email] = $input->email;
				}
			}
			if (!empty($input->alternate_email)) {
				if (isset($name)) {
					$emails[$input->alternate_email] = $name . ' (' . __('alternate') . ')';
				} else {
					$emails[$input->alternate_email] = $input->alternate_email;
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
		} else if (is_array($input) && array_key_exists('id', $input)) {
			// If it's an array with an ID field, issue an error; we want to deal with entities only.
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
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
			$emails = [$input];
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

	protected function _handlePersonSearch(array $url_params = [], array $conditions = []) {
		if ($this->request->is('ajax')) {
			$this->viewBuilder()->className('Ajax.Ajax');
		}

		list($params, $url) = $this->_extractSearchParams($url_params);

		if (!empty($params)) {
			$names = [];
			foreach (['first_name', 'last_name'] as $field) {
				if (!empty($params[$field])) {
					$names[] = trim($params[$field], ' *');
				}
			}
			$test = implode('', $names);
			$min = (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) ? 1 : 2;
			if (strlen($test) < $min) {
				$this->set('error', __('The search terms used are too general. Please be more specific.'));
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

					// Add each element of the search string one by one
					foreach(explode(' ', $value) as $str) {
						$term = "People.$field";
						if ($str) {
							if (strpos($str, '*') !== false) {
								$term .= ' LIKE';
								$str = strtr($str, '*', '%');
							}
							$search_conditions[] = [$term => $str];
						}
					}
				}
				$query->where($search_conditions);

				// Match people in the affiliate, or admins who are effectively in all
				if (array_key_exists('affiliate_id', $params)) {
					$admins = $this->People->find()
						->hydrate(false)
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

				if (array_key_exists('group_id', $conditions)) {
					$query->matching('Groups', function (Query $q) use ($conditions) {
						return $q->where(['Groups.id IN' => $conditions['group_id']]);
					});
				}

				$this->set('people', $this->paginate($query));
			}
		}
		$this->set(compact('url'));
	}

	protected function _extractSearchParams(array $url_params = []) {
		if ($this->request->is('post')) {
			$params = $url = array_merge($this->request->data, $this->request->getQueryParams());
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

		if (!$this->request->is('post')) {
			$this->request->data = $params;
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
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		if (!collection($groups)->some(function ($group) {
			return $group->id == GROUP_PLAYER;
		})) {
			return false;
		}

		if (!is_a($person->birthdate, 'Cake\Chronos\ChronosInterface')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
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
		if ($request->is('ajax')) {
			$url = $request->referer(true);
		} else {
			$url = $request->here();
		}
		if (empty($url)) {
			$url = '/';
		}
		return \App\Lib\base64_url_encode($url);
	}

}
