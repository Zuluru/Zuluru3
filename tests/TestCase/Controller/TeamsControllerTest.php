<?php
namespace App\Test\TestCase\Controller;

use App\PasswordHasher\HasherTrait;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\TeamsController Test Case
 */
class TeamsControllerTest extends ControllerTestCase {

	use HasherTrait;

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		// Anyone is allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'index']);
	}

	/**
	 * Test letter method
	 *
	 * @return void
	 */
	public function testLetter(): void {
		// Anyone is allowed to see the list by letter
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'letter', 'letter' => 'R']);
	}

	/**
	 * Test join method
	 *
	 * @return void
	 */
	public function testJoin(): void {
		// Anyone logged in is allowed to try to find teams to join
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK], PERSON_ID_VISITOR);

		// Others are not allowed to join teams
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'join', 'team' => TEAM_ID_BLACK]);
	}

	/**
	 * Test unassigned method
	 *
	 * @return void
	 */
	public function testUnassigned(): void {
		// Admins are allowed to see the unassigned teams list
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_ADMIN);

		// Managers are allowed to see the unassigned teams list
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_MANAGER);

		// Others are not allowed to see the unassigned teams list
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'unassigned']);
	}

	/**
	 * Test statistics method
	 *
	 * @return void
	 */
	public function testStatistics(): void {
		// Admins are allowed to view statistics
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_ADMIN);

		// Managers are allowed to view statistics
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_MANAGER);

		// Others are not allowed to view statistics
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'statistics']);
	}

	/**
	 * Test compareAffiliateAndCount method
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCount(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView(): void {
		// Admins are allowed to view teams, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		// The strings for edit are all longer here than other places, because there can be simple edit links in help text.
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams/delete?team=' . TEAM_ID_RED);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BEARS], PERSON_ID_ADMIN);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_BEARS);
		$this->assertResponseContains('/teams/delete?team=' . TEAM_ID_BEARS);

		// Managers are allowed to view teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams/delete?team=' . TEAM_ID_RED);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_BEARS);
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_BEARS);

		// Coordinators are allowed to view teams but cannot edit
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_RED);

		// Captains are allowed to view and edit their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		// TODO: Test that captains can delete their own teams when the registration module is turned off
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_RED);

		// Others are allowed to view teams, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_RED);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_RED);

		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED]);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . TEAM_ID_RED);
		$this->assertResponseNotContains('/teams/delete?team=' . TEAM_ID_RED);
	}

	/**
	 * Test numbers method as an admin
	 *
	 * @return void
	 */
	public function testNumbersAsAdmin(): void {
		// Admins are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a manager
	 *
	 * @return void
	 */
	public function testNumbersAsManager(): void {
		// Managers are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a captain
	 *
	 * @return void
	 */
	public function testNumbersAsCaptain(): void {
		// Captains are allowed to set numbers before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		// But not after
		FrozenDate::setTestNow(new FrozenDate('November 1'));
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The roster deadline for this division has already passed.');

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a coordinator
	 *
	 * @return void
	 */
	public function testNumbersAsCoordinator(): void {
		// Coordinators are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a player
	 *
	 * @return void
	 */
	public function testNumbersAsPlayer(): void {
		// Players are allowed to set only their own number
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED, 'person' => PERSON_ID_PLAYER], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as others
	 *
	 * @return void
	 */
	public function testNumbersAsOthers(): void {
		// Others are not allowed to set numbers
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'numbers', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test stats method
	 *
	 * @return void
	 */
	public function testStats(): void {
		// Anyone logged in is allowed to see stats
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_VISITOR);

		// Others are not allowed to see stats
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'stats', 'team' => TEAM_ID_CHICKADEES]);
	}

	/**
	 * Test stat_sheet method
	 *
	 * @return void
	 */
	public function testStatSheet(): void {
		// Admins are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_ADMIN);

		// Managers are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_MANAGER);

		// Coordinators are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_COORDINATOR);

		// Captains are allowed to see the stat sheet for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_SPARROWS], PERSON_ID_CAPTAIN);

		// Others are not allowed to see the stat sheet
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', 'team' => TEAM_ID_CHICKADEES]);
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip(): void {
		// Anyone is allowed to view team tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/schedule?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'tooltip', 'team' => 0],
			PERSON_ID_ADMIN, ['controller' => 'Teams', 'action' => 'index'],
			'Invalid team.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/schedule?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/schedule?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin(): void {
		// Admins are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers(): void {
		// Others are not allowed to add teams
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			PERSON_ID_COORDINATOR, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			PERSON_ID_CAPTAIN, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			PERSON_ID_PLAYER, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			PERSON_ID_VISITOR, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to  teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator(): void {
		// Coordinators are not allowed to edit teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain(): void {
		// Captains are allowed to edit their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_CAPTAIN);

		// But not others
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_SPARROWS], PERSON_ID_CAPTAIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'edit', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test note method as an admin
	 *
	 * @return void
	 */
	public function testNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 *
	 * @return void
	 */
	public function testNoteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 *
	 * @return void
	 */
	public function testNoteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all coordinators to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_COORDINATOR,
				'note' => 'This is a coordinator note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is a coordinator note.');
	}

	/**
	 * Test note method as a captain
	 *
	 * @return void
	 */
	public function testNoteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_YELLOW],
			PERSON_ID_CAPTAIN4, [
				'team_id' => TEAM_ID_YELLOW,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', 'team' => TEAM_ID_YELLOW], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_YELLOW], PERSON_ID_CAPTAIN4);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_YELLOW], [PERSON_ID_PLAYER, PERSON_ID_CHILD]);
		$this->assertResponseNotContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN2, [
				'team_id' => TEAM_ID_BLUE,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', 'team' => TEAM_ID_BLUE], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE], PERSON_ID_CAPTAIN2);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE], [PERSON_ID_PLAYER, PERSON_ID_CHILD]);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as a player
	 *
	 * @return void
	 */
	public function testNoteAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Players are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, [
				'team_id' => TEAM_ID_RED,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', 'team' => TEAM_ID_RED], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as someone else
	 *
	 * @return void
	 */
	public function testNoteAsVisitor(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Visitors are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method without being logged in
	 *
	 * @return void
	 */
	public function testNoteAsAnonymous(): void {
		// Others are not allowed to add notes
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'note', 'team' => TEAM_ID_RED]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as an admin
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_ADMIN, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_ADMIN, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_ADMIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a manager
	 *
	 * @return void
	 */
	public function testDeleteNoteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_MANAGER, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_MANAGER, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to delete coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_COORDINATOR, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_COORDINATOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a captain
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_CAPTAIN, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_CAPTAIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a player
	 *
	 * @return void
	 */
	public function testDeleteNoteAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Players are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_PLAYER, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_PLAYER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as someone else
	 *
	 * @return void
	 */
	public function testDeleteNoteAsVisitor(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Visitors are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_VISITOR],
			PERSON_ID_VISITOR, [], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_ADMIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_COORDINATOR],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_CAPTAIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER],
			PERSON_ID_VISITOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as others
	 *
	 * @return void
	 */
	public function testDeleteNoteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete notes
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', 'note' => NOTE_ID_TEAM_RED_PLAYER]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete teams
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [], ['controller' => 'Teams', 'action' => 'index'],
			'#The following records reference this team, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete teams in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_BEARS],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are not allowed to delete teams
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_ORANGE],
			PERSON_ID_COORDINATOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Team owners are allowed to delete their own teams
		/* TODO: Not at this time
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');
		*/

		// But not others
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete teams
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS], PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS], PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS]);
	}

	/**
	 * Test move method as an admin
	 *
	 * @return void
	 */
	public function testMoveAsAdmin(): void {
		// Admins are allowed to move teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a manager
	 *
	 * @return void
	 */
	public function testMoveAsManager(): void {
		// Managers are allowed to move teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a coordinator
	 *
	 * @return void
	 */
	public function testMoveAsCoordinator(): void {
		// Coordinators are not allowed to move teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as others
	 *
	 * @return void
	 */
	public function testMoveAsOthers(): void {
		// Others are not allowed to move teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'move', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test schedule method
	 *
	 * @return void
	 */
	public function testSchedule(): void {
		// Anyone is allowed to see the schedule
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseNotContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseNotContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseNotContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseNotContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'schedule', 'team' => TEAM_ID_RED]);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseContains('/games/view?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertResponseNotContains('/team_events/view?event=' . TEAM_EVENT_ID_RED_PRACTICE);
	}

	/**
	 * Test ical method
	 *
	 * @return void
	 */
	public function testIcal(): void {
		// Make sure that we're before the division close date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Can get the ical feed for any team in an active or upcoming league, but not in the past
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'ical', TEAM_ID_RED]);

		$this->get(['controller' => 'Teams', 'action' => 'ical', TEAM_ID_RED_PAST]);
		$this->assertResponseCode(410);
	}

	/**
	 * Test spirit method
	 *
	 * @return void
	 */
	public function testSpirit(): void {
		// Admins are allowed to see the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);

		// Managers are allowed to see the spirit report for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);

		// Coordinators are allowed to see the spirit report for teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_OAKS], PERSON_ID_COORDINATOR);

		// Others are not allowed to see the spirit report
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'spirit', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test attendance method
	 *
	 * @return void
	 */
	public function testAttendance(): void {
		// Admins are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);

		// Managers are allowed to see attendance for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);

		// Coordinators are not allowed to see attendance, even for teams in their divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_OAKS], PERSON_ID_COORDINATOR);

		// Captains are allowed to see attendance for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		// And attendance for teams of people they're related to
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_BLUE], PERSON_ID_CAPTAIN);
		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_SPARROWS], PERSON_ID_CAPTAIN);

		// Players are allowed to see attendance for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		// And attendance for teams of people they're related to
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_BLUE], PERSON_ID_PLAYER);
		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_CHICKADEES], PERSON_ID_PLAYER);

		// Others are not allowed to see attendance
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'attendance', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test emails method
	 *
	 * @return void
	 */
	public function testEmails(): void {
		// Admins are allowed to see emails
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);

		// Managers are allowed to see emails for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);

		// Captains are allowed to see emails for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_BLUE], PERSON_ID_CAPTAIN);

		// Others are not allowed to see emails
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'emails', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add_player method as an admin
	 *
	 * @return void
	 */
	public function testAddPlayerAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'player',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/teams/add_player?team=' . TEAM_ID_OAKS));
		$this->assertResponseContains('/teams/roster_add?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;team=' . TEAM_ID_OAKS);
	}

	/**
	 * Test add_player method as a manager
	 *
	 * @return void
	 */
	public function testAddPlayerAsManager(): void {
		// Managers are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS], PERSON_ID_MANAGER);

		// But not teams in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_LIONS], PERSON_ID_MANAGER);
	}

	/**
	 * Test add_player method as a coordinator
	 *
	 * @return void
	 */
	public function testAddPlayerAsCoordinator(): void {
		// Coordinators are allowed to add players to teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);

		// But not other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_MAPLES], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add_player method as a captain
	 *
	 * @return void
	 */
	public function testAddPlayerAsCaptain(): void {
		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players to their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_MAPLES], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test add_player method as others
	 *
	 * @return void
	 */
	public function testAddPlayerAsOthers(): void {
		// Others are not allowed to add players to teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add_from_team method as an admin
	 *
	 * @return void
	 */
	public function testAddFromTeamAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add from team
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, ['team' => TEAM_ID_RED_PAST]);
		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_from_team method as a manager
	 *
	 * @return void
	 */
	public function testAddFromTeamAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add from team to teams in their affiliate
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, ['team' => TEAM_ID_RED_PAST]);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_BEARS],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFromTeamAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add from team to teams in their divisions
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, ['team' => TEAM_ID_RED_PAST]);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_OAKS],
			PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as a captain
	 *
	 * @return void
	 */
	public function testAddFromTeamAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players from their past teams
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['team' => TEAM_ID_RED_PAST]);
		$this->assertResponseContains('<span id="people_person_' .  PERSON_ID_CHILD . '" class="trigger">Carla Child</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_CHILD . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_CHILD . '-role-captain">\s*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_CHILD . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_CHILD . '-position-unspecified" checked="checked">\s*Unspecified#ms');
		$this->assertResponseContains('<span id="people_person_' .  PERSON_ID_MANAGER . '" class="trigger">Mary Manager</span>');
		// The manager is not a player, so doesn't get player options, just coach and none
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_MANAGER . '\]\[role\]" value="coach" id="player-' .  PERSON_ID_MANAGER . '-role-coach">\s*Non-playing coach#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_MANAGER . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_MANAGER . '-position-unspecified" checked="checked">\s*Unspecified#ms');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, [
				'team' => TEAM_ID_RED_PAST,
				'player' => [
					PERSON_ID_CHILD => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					PERSON_ID_MANAGER => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'Invitation has been sent to Carla Child.');

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Invitation to join Red', $messages[0]);
		$this->assertContains('Crystal Captain has invited you to join the roster of the Test Zuluru Affiliate team Red as a Regular player.', $messages[0]);
		$this->assertContains('Red plays in the Competitive division of the Monday Night league, which operates on Monday.', $messages[0]);
		$this->assertRegExp('#More details about Red may be found at\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Regular player [invited:');
		// There is no accept link, because the membership is not yet paid for
		$this->assertResponseNotContains('/teams/roster_accept?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_CHILD);
		$this->assertResponseContains('/teams/roster_decline?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_CHILD);
		$this->assertResponseNotContains('/teams/roster_accept?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/teams/roster_decline?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_MANAGER);
	}

	/**
	 * Test add_from_team method as others
	 *
	 * @return void
	 */
	public function testAddFromTeamAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to add from team
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add_from_event method as an admin
	 *
	 * @return void
	 */
	public function testAddFromEventAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players from events
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, ['event' => EVENT_ID_MEMBERSHIP]);
		$this->assertResponseContains('<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, [
				'event' => EVENT_ID_MEMBERSHIP,
				'player' => [
					PERSON_ID_PLAYER => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Coordinator will not be added; the registration is not paid
					PERSON_ID_COORDINATOR => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					PERSON_ID_CHILD => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Captain2 will not be added; the role is "none"
					PERSON_ID_CAPTAIN2 => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS],
			'Pam Player and Carla Child have been added to the roster.');

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(2, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: You have been added to Oaks', $messages[0]);
		$this->assertContains('You have been added to the roster of the Test Zuluru Affiliate team Oaks as a Regular player.', $messages[0]);
		$this->assertContains('Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.', $messages[0]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[0]);

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[1]);
		$this->assertContains('Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;', $messages[1]);
		// To line still says Pam, because the child has no email address listed
		$this->assertContains('To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[1]);
		$this->assertNotContains('CC: ', $messages[1]);
		$this->assertContains('Subject: You have been added to Oaks', $messages[1]);
		$this->assertContains('You have been added to the roster of the Test Zuluru Affiliate team Oaks as a Regular player.', $messages[1]);
		$this->assertContains('Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.', $messages[1]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[1]);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseContains('Regular player');
		$this->assertResponseContains('/teams/roster_role?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/teams/roster_role?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_CHILD);
		$this->assertResponseNotContains('/teams/roster_role?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('/teams/roster_role?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_CAPTAIN2);
	}

	/**
	 * Test add_from_event method as a manager
	 *
	 * @return void
	 */
	public function testAddFromEventAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add players from events
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, ['event' => EVENT_ID_MEMBERSHIP]);
		$this->assertResponseContains('<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');

		// But not to teams in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_BEARS],
			PERSON_ID_MANAGER, [
				'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB,
			]);
	}

	/**
	 * Test add_from_event method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFromEventAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add players from events to teams in their divisions
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_CHICKADEES],
			PERSON_ID_COORDINATOR, ['event' => EVENT_ID_MEMBERSHIP]);
		$this->assertResponseContains('<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');

		// But not other divisions
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_COORDINATOR, [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
	}

	/**
	 * Test add_from_event method as others
	 *
	 * @return void
	 */
	public function testAddFromEventAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for adding players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Others are not allowed to add players from events
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test roster_role method as an admin
	 *
	 * @return void
	 */
	public function testRosterRoleAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to change roster roles
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_ADMIN, ['role' => 'captain']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a manager
	 *
	 * @return void
	 */
	public function testRosterRoleAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to change roster roles for teams in their affiliate
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_MANAGER, ['role' => 'captain']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterRoleAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Coordinators are allowed to change roster roles for teams in their divisions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_COORDINATOR, ['role' => 'captain']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a captain
	 *
	 * @return void
	 */
	public function testRosterRoleAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CAPTAIN, ['role' => 'substitute']);

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_COORDINATOR, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'This person is not on this team.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'A player\'s role on a team cannot be changed until they have been approved on the roster.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CAPTAIN, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'All teams must have at least one player as coach or captain.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN2, ['role' => 'substitute']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as a player
	 *
	 * @return void
	 */
	public function testRosterRoleAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED_PAST],
			'The roster deadline for this division has already passed.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['role' => 'captain'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE],
			'You do not have permission to set that role.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['role' => 'substitute']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as someone else
	 *
	 * @return void
	 */
	public function testRosterRoleAsVisitor(): void {
		// Others are not allowed to change roster roles
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE]);
	}

	/**
	 * Test roster_position method as an admin
	 *
	 * @return void
	 */
	public function testRosterPositionAsAdmin(): void {
		$this->enableCsrfToken();

		// Admins are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_ADMIN, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a manager
	 *
	 * @return void
	 */
	public function testRosterPositionAsManager(): void {
		$this->enableCsrfToken();

		// Managers are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_MANAGER, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterPositionAsCoordinator(): void {
		$this->enableCsrfToken();

		// Coordinators are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_COORDINATOR, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a captain
	 *
	 * @return void
	 */
	public function testRosterPositionAsCaptain(): void {
		$this->enableCsrfToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CAPTAIN, ['position' => 'handler']);

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_COORDINATOR, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'This person is not on this team.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN2, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as a player
	 *
	 * @return void
	 */
	public function testRosterPositionAsPlayer(): void {
		$this->enableCsrfToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED_PAST],
			'The roster deadline for this division has already passed.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['position' => 'xyz'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE],
			'That is not a valid position.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD], ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as others
	 *
	 * @return void
	 */
	public function testRosterPositionAsOthers(): void {
		$this->enableCsrfToken();

		// Others are not allowed to change roster positions
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_VISITOR, ['position' => 'handler']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			['position' => 'handler']);
	}

	/**
	 * Test roster_add method as an admin
	 *
	 * @return void
	 */
	public function testRosterAddAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseContains('/teams/roster_add?person=' . PERSON_ID_PLAYER . '&amp;team=' . TEAM_ID_OAKS);

		// Submit an empty add form
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN, []);
		$this->assertResponseContains('You must select a role for this person.');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS]);

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		// TODO: Why is this an invitation, when add_from_event is a direct add?
		$this->assertContains('Subject: Invitation to join Oaks', $messages[0]);
		$this->assertContains('Amy Administrator has invited you to join the roster of the Test Zuluru Affiliate team Oaks as a Regular player.', $messages[0]);
		$this->assertContains('Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.', $messages[0]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseContains('Regular player [invited:');
		$this->assertResponseContains('/teams/roster_accept?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/teams/roster_decline?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER);

		// TODO: Check all the potential emails and different states that can be generated in other situations: add vs invite, admin vs captain, etc.
	}

	/**
	 * Test roster_add method as a manager
	 *
	 * @return void
	 */
	public function testRosterAddAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_OAKS], PERSON_ID_MANAGER);
		$this->assertResponseContains('/teams/roster_add?person=' . PERSON_ID_CHILD . '&amp;team=' . TEAM_ID_OAKS);

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS]);

		// But not teams in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_LIONS], PERSON_ID_MANAGER);
	}

	/**
	 * Test roster_add method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterAddAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add players to teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_MANAGER, 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/teams/roster_add?person=' . PERSON_ID_MANAGER . '&amp;team=' . TEAM_ID_RED);

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_MANAGER, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED]);

		// But not other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_MAPLES], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test roster_add method as a captain
	 *
	 * @return void
	 */
	public function testRosterAddAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players to their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/teams/roster_add?person=' . PERSON_ID_CHILD . '&amp;team=' . TEAM_ID_RED);

		// Submit the add form
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN, [
			'role' => 'player',
			'position' => 'unspecified',
		], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED]);

		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_MAPLES], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test roster_add method as others
	 *
	 * @return void
	 */
	public function testRosterAddAsOthers(): void {
		// Others are not allowed to add players to teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test roster_request method as a captain
	 *
	 * @return void
	 */
	public function testRosterRequestAsCaptain(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to request to join a team
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as a player
	 *
	 * @return void
	 */
	public function testRosterRequestAsPlayer(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Players are allowed to request to join a team
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as someone else
	 *
	 * @return void
	 */
	public function testRosterRequestAsVisitor(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Visitors are allowed to request to join a team
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as others
	 *
	 * @return void
	 */
	public function testRosterRequestAsOthers(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Others (any non-players) are not allowed to request to join a team
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_ADMIN);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK], PERSON_ID_COORDINATOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', 'team' => TEAM_ID_BLACK]);
	}

	/**
	 * Test roster_accept method as an admin
	 *
	 * @return void
	 */
	public function testRosterAcceptAsAdmin(): void {
		// Admins are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have accepted this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a manager
	 *
	 * @return void
	 */
	public function testRosterAcceptAsManager(): void {
		// Managers are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have accepted this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterAcceptAsCoordinator(): void {
		// Coordinators are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have accepted this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a captain
	 *
	 * @return void
	 */
	public function testRosterAcceptAsCaptain(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are not allowed to accept roster invitations to their players
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You are not allowed to accept this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a player
	 *
	 * @return void
	 */
	public function testRosterAcceptAsPlayer(): void {
		// Cannot accept invites after the roster deadline!
		FrozenDate::setTestNow(new FrozenDate('November 1'));
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_PLAYER . '.*Regular player#ms');

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_PLAYER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED_PAST],
			'This person has already been added to the roster.');
	}

	/**
	 * Test roster_accept method with a code
	 *
	 * @return void
	 */
	public function testRosterAcceptWithCode(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$roster = TableRegistry::get('TeamsPeople')->find()->where(['person_id' => PERSON_ID_PLAYER, 'team_id' => TEAM_ID_RED])->firstOrFail();
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED,
			'code' => $this->_makeHash([$roster->id, TEAM_ID_RED, PERSON_ID_PLAYER, $roster->role, $roster->created])],
			['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have accepted this roster invitation.');
	}

	/**
	 * Test roster_accept method as others
	 *
	 * @return void
	 */
	public function testRosterAcceptAsOthers(): void {
		// Others are not allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You are not allowed to accept this roster invitation.');

		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED, 'code' => 'wrong'],
			['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The authorization code is invalid.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test roster_decline method as an admin
	 *
	 * @return void
	 */
	public function testRosterDeclineAsAdmin(): void {
		// Admins are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have declined this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a manager
	 *
	 * @return void
	 */
	public function testRosterDeclineAsManager(): void {
		// Managers are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have declined this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterDeclineAsCoordinator(): void {
		// Coordinators are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have declined this roster invitation.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a captain
	 *
	 * @return void
	 */
	public function testRosterDeclineAsCaptain(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to remove roster invitations to their players
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have declined this roster invitation.');

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a player
	 *
	 * @return void
	 */
	public function testRosterDeclineAsPlayer(): void {
		// Cannot decline invites after the roster deadline!
		FrozenDate::setTestNow(new FrozenDate('November 1'));
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
		$error = [
			'error' => null,
			'content' => '',
			'_message' => null,
		];
		$this->assertEquals(json_encode($error), (string)$this->_response->getBody());

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_PLAYER, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED_PAST],
			'This person has already been added to the roster.');
	}

	/**
	 * Test roster_accept method with a code
	 *
	 * @return void
	 */
	public function testRosterDeclineWithCode(): void {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$roster = TableRegistry::get('TeamsPeople')->find()->where(['person_id' => PERSON_ID_PLAYER, 'team_id' => TEAM_ID_RED])->firstOrFail();
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED,
			'code' => $this->_makeHash([$roster->id, TEAM_ID_RED, PERSON_ID_PLAYER, $roster->role, $roster->created])],
			['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You have declined this roster invitation.');
	}

	/**
	 * Test roster_decline method as someone else
	 *
	 * @return void
	 */
	public function testRosterDeclineAsOthers(): void {
		// Others are not allowed to decline roster invitations
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR, ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'You are not allowed to decline this roster invitation.', 'warning');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED]);
	}

}
