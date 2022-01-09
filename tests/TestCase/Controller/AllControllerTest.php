<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\AllController Test Case
 */
class AllControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.RosterRoles',
		'app.Settings',
	];

	/**
	 * Test clear_cache method
	 *
	 * @return void
	 */
	public function testClearCache() {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to clear the cache
		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'clear_cache'],
			$admin->id, '/',
			'The cache has been cleared.');

		// Others are not allowed to clear the cache
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'All', 'action' => 'clear_cache'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'All', 'action' => 'clear_cache']);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method
	 *
	 * @return void
	 */
	public function testLanguage() {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone is allowed to set their language for the session
		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			$admin->id, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			$manager->id, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			$volunteer->id, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		$this->assertGetAsAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			$player->id, '/',
			'Your language has been changed for this session. To change it permanently, {0}.');
		$this->assertCookie('en_US', 'ZuluruLocale');

		// Others are allowed to set their language for the session
		$this->assertGetAnonymousAccessRedirect(['controller' => 'All', 'action' => 'language', 'lang' => 'en_US'],
			'/');
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
		$team = TeamFactory::make()->persist();

		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->get(['controller' => 'Teams', 'action' => 'ical', $team->id]);
		$this->assertResponseCode(410);
	}

	/**
	 * 2a. Unauthenticated access to a protected resource (e.g. an edit page or admin-only view), via HTTP.
	 * Should redirect to login, include URL back to the thing, and set "you must log in" message.
	 *
	 * @return void
	 */
	public function testAuth2aUnauthenticatedAccessToProtectedResourceHTTPForbiddenException() {
		$team = TeamFactory::make()->persist();

		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => $team->id]);
	}

	/**
	 * 2b. Unauthenticated access to a protected resource, via Ajax.
	 * Should redirect to login, include URL back to the thing, and set "you must log in" message, but in JSON.
	 *
	 * @return void
	 */
	public function testAuth2bUnauthenticatedAccessToProtectedResourceAjax() {
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL]);
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
		$player = PersonFactory::makePlayer()
			->with('TeamsPeople', TeamsPersonFactory::make(['status' => ROSTER_INVITED])->with('Teams'))
			->persist();

		$team = $player->teams_people[0]->team;
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id, 'code' => 'wrong'],
			['controller' => 'Teams', 'action' => 'view', 'team' => $team->id],
			'The authorization code is invalid.');

		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id],
			$player->id);
	}

	/**
	 * 4a. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the ForbiddenException.
	 *
	 * @return void
	 */
	public function testAuth4aUnauthorizedAccessToProtectedResourceHTTPForbiddenException() {
		$player = PersonFactory::makePlayer()->with('Teams')->persist();

		$team = $player->teams[0];
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => $team->id], $player->id);
	}

	/**
	 * 4b. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the ForbiddenException.
	 *
	 * @return void
	 */
	public function testAuth4bUnauthorizedAccessToProtectedResourceAjaxForbiddenException() {
		$player = PersonFactory::makePlayer()->persist();

		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			$player->id);
	}

	/**
	 * 4c. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the MissingIdentityException.
	 *
	 * @return void
	 */
	public function testAuth4cUnauthorizedAccessToProtectedResourceHTTPMissingIdentityException() {
		$player = PersonFactory::makePlayer()->with('Teams')->persist();

		$team = $player->teams[0];
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id]);
	}

	/**
	 * 4d. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to home page. Will set a message (typically "you do not have permission").
	 * Handled via custom authorization handler on the MissingIdentityException.
	 *
	 * @return void
	 */
	public function testAuth4cUnauthorizedAccessToProtectedResourceAjaxMissingIdentityException() {
		$player = PersonFactory::makePlayer()->with('Teams')->persist();

		$team = $player->teams[0];
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id]);
	}

	/**
	 * 5a. Unauthorized access to a missing resource (e.g. a disabled feature)
	 * Should redirect to somewhere (e.g. a public view). May set a message.
	 *
	 * @return void
	 */
	public function testAuth5aUnauthorizedAccessToMissingResource() {
		$team = TeamFactory::make()->with('Divisions.Leagues')->persist();
		$manager = PersonFactory::makeManager()->persist();

		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'stats', 'team' => $team->id],
			$manager->id, ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id],
			'This league does not have stat tracking enabled.');
	}

	/**
	 * 5b. Unauthorized 'get' access (e.g. an edit page or admin-only view)
	 * Should redirect to somewhere (e.g. a public view). Will set a custom message.
	 * Handled via custom authorization handler on the custom ForbiddenRedirectException.
	 *
	 * @return void
	 */
	public function testAuth5bUnauthorizedAccessToProtectedResourceHTTPForbiddenRedirectException() {
		$captain = PersonFactory::makePlayer()
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'captain'])->with('Teams'))
			->persist();

		$team = $captain->teams_people[0]->team;

		$player = PersonFactory::makePlayer()
			->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'status' => ROSTER_INVITED]))
			->persist();

		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id],
			$captain->id, ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id],
			'You are not allowed to accept this roster invitation.');
	}

	/**
	 * 5c. Unauthorized 'get' access (e.g. an edit page or admin-only view) via Ajax
	 * Should redirect to somewhere (e.g. a public view). Will set a custom message.
	 * Handled via custom authorization handler on the custom ForbiddenRedirectException.
	 *
	 * @return void
	 */
	public function testAuth5cUnauthorizedAccessToProtectedResourceAjaxForbiddenRedirectException() {
		$captain = PersonFactory::makePlayer()
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'captain'])->with('Teams'))
			->persist();

		$team = $captain->teams_people[0]->team;

		$player = PersonFactory::makePlayer()
			->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'status' => ROSTER_INVITED]))
			->persist();

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => $player->id, 'team' => $team->id],
			$captain->id, ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id],
			'You are not allowed to accept this roster invitation.', 'warning');
	}

}
