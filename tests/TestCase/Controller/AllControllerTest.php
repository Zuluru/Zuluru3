<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenDate;

/**
 * App\Controller\AllController Test Case
 */
class AllControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
				'app.uploads',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
						'app.teams_facilities',
					'app.divisions_days',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.divisions_people',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.stats',
				'app.leagues_stat_types',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
			'app.categories',
				'app.tasks',
					'app.task_slots',
			'app.badges',
				'app.badges_people',
			'app.notes',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test clear_cache method as an admin
	 *
	 * @return void
	 */
	public function testClearCacheAsAdmin() {
		// Admins are allowed to clear the cache
		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'clear_cache'],
			PERSON_ID_ADMIN, '/',
			'The cache has been cleared.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as others
	 *
	 * @return void
	 */
	public function testClearCacheAsOthers() {
		// Others are not allowed to clear the cache
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'All', 'action' => 'clear_cache']);
	}

	/**
	 * Test language method
	 *
	 * @return void
	 */
	public function testLanguage() {
		// Anyone is allowed to set their language for the session
		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_ADMIN, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_MANAGER, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_COORDINATOR, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_CAPTAIN, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_PLAYER, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			PERSON_ID_VISITOR, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		// Others are allowed to set their language for the session
		$this->assertGetAnonymousAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			'/', false);
		$this->assertCookie('en_US', 'ZuluruLocale');
	}

	/**
	 * Set of methods to test all the various authentication and authorization failure scenarios with a single PHPUnit filter
	 */

	/**
	 * 1. Unauthenticated access to an obsolete public resource (e.g. a deactivated user profile).
	 * Should respond with HTTP code 410 GONE.
	 *
	 * @return void
	 */
	public function testAuth1UnauthenticatedAccessToObsoleteResource() {
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->get(['controller' => 'Teams', 'action' => 'ical', TEAM_ID_RED_PAST]);
		$this->assertResponseCode(410);
	}

	/**
	 * 2a. Unauthenticated access to a protected resource (e.g. an edit page or admin-only view), via HTTP.
	 * Should redirect to login, include URL back to the thing, and set "you must log in" message.
	 *
	 * @return void
	 */
	public function testAuth2aUnauthenticatedAccessToProtectedResourceHTTPForbiddenException() {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED]);
	}

	/**
	 * 2b. Unauthenticated access to a protected resource, via Ajax.
	 * Should redirect to login, include URL back to the thing, and set "you must log in" message, but in JSON.
	 *
	 * @return void
	 */
	public function testAuth2bUnauthenticatedAccessToProtectedResourceAjax() {
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_ID_OFFICIAL]);
	}

	/**
	 * 3a. Unauthenticated access to a protected view of a public resource (e.g. registration wizard).
	 * Should redirect to somewhere (e.g. a public view).
	 *
	 * @return void
	 */
	public function testAuth3aUnauthenticatedAccessToProtectedViewOfPublicResource() {
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Events', 'action' => 'wizard'],
			['controller' => 'Events', 'action' => 'index']);
	}

	/**
	 * 3b. Unauthenticated access to a protected resource, via HTTP.
	 * Should redirect to somewhere, and set a custom message.
	 *
	 * @return void
	 */
	public function testAuth3bUnauthenticatedAccessToProtectedResourceHTTPForbiddenRedirectException() {
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED, 'code' => 'wrong'],
			['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The authorization code is invalid.');

		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
	}

	/**
	 * 4a. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the ForbiddenException.
	 *
	 * @return void
	 */
	public function testAuth4aUnauthorizedAccessToProtectedResourceHTTPForbiddenException() {
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
	}

	/**
	 * 4b. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the ForbiddenException.
	 *
	 * @return void
	 */
	public function testAuth4bUnauthorizedAccessToProtectedResourceAjaxForbiddenException() {
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_ID_OFFICIAL],
			PERSON_ID_PLAYER);
	}

	/**
	 * 4c. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the MissingIdentityException.
	 *
	 * @return void
	 */
	public function testAuth4cUnauthorizedAccessToProtectedResourceHTTPMissingIdentityException() {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED]);
	}

	/**
	 * 4d. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the MissingIdentityException.
	 *
	 * @return void
	 */
	public function testAuth4cUnauthorizedAccessToProtectedResourceAjaxMissingIdentityException() {
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED]);
	}

	/**
	 * 5a. Unauthorized access to a missing resource (e.g. a disabled feature)
	 * Should redirect to somewhere (e.g. a public view). May set a message.
	 *
	 * @return void
	 */
	public function testAuth5aUnauthorizedAccessToMissingResource() {
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'This league does not have stat tracking enabled.');
	}

	/**
	 * 5a. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to somewhere (e.g. a public view). Will set a custom message.
	 * Handled via custom authorization handler on the custom ForbiddenRedirectException.
	 *
	 * @return void
	 */
	public function testAuth5aUnauthorizedAccessToProtectedResourceHTTPForbiddenRedirectException() {
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You are not allowed to accept this roster invitation.');
	}

	/**
	 * 5b. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to somewhere (e.g. a public view). Will set a custom message.
	 * Handled via custom authorization handler on the custom ForbiddenRedirectException.
	 *
	 * @return void
	 */
	public function testAuth5bUnauthorizedAccessToProtectedResourceAjaxForbiddenRedirectException() {
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You are not allowed to accept this roster invitation.', 'warning');
	}

}
