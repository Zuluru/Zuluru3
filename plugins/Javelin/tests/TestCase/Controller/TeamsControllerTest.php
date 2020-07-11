<?php
namespace Javelin\Test\TestCase\Controller;

use Javelin\Controller\TeamsController;

/**
 * Javelin\Controller\TeamsController Test Case
 */
class TeamsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
        'plugin.javelin.Teams',
        'plugin.javelin.Divisions',
        'plugin.javelin.DivisionsNameTranslation',
        'plugin.javelin.DivisionsHeaderTranslation',
        'plugin.javelin.DivisionsFooterTranslation',
        'plugin.javelin.I18n',
        'plugin.javelin.Leagues',
        'plugin.javelin.LeaguesNameTranslation',
        'plugin.javelin.Affiliates',
        'plugin.javelin.AffiliatesNameTranslation',
        'plugin.javelin.Badges',
        'plugin.javelin.BadgesNameTranslation',
        'plugin.javelin.BadgesDescriptionTranslation',
        'plugin.javelin.People',
        'plugin.javelin.Users',
        'plugin.javelin.GamesAllstars',
        'plugin.javelin.ScoreEntries',
        'plugin.javelin.Games',
        'plugin.javelin.GameSlots',
        'plugin.javelin.Fields',
        'plugin.javelin.FieldsNumTranslation',
        'plugin.javelin.Facilities',
        'plugin.javelin.FacilitiesNameTranslation',
        'plugin.javelin.FacilitiesCodeTranslation',
        'plugin.javelin.FacilitiesDrivingDirectionsTranslation',
        'plugin.javelin.FacilitiesParkingDetailsTranslation',
        'plugin.javelin.FacilitiesTransitDirectionsTranslation',
        'plugin.javelin.FacilitiesBikingDirectionsTranslation',
        'plugin.javelin.FacilitiesWashroomsTranslation',
        'plugin.javelin.FacilitiesPublicInstructionsTranslation',
        'plugin.javelin.FacilitiesSiteInstructionsTranslation',
        'plugin.javelin.FacilitiesSponsorTranslation',
        'plugin.javelin.Regions',
        'plugin.javelin.RegionsNameTranslation',
        'plugin.javelin.TeamsFacilities',
        'plugin.javelin.Notes',
        'plugin.javelin.CreatedTeam',
        'plugin.javelin.Attendances',
        'plugin.javelin.TeamEvents',
        'plugin.javelin.AttendanceReminderEmails',
        'plugin.javelin.AttendanceSummaryEmails',
        'plugin.javelin.Incidents',
        'plugin.javelin.SpiritEntries',
        'plugin.javelin.MostSpirited',
        'plugin.javelin.Credits',
        'plugin.javelin.Preregistrations',
        'plugin.javelin.Events',
        'plugin.javelin.EventsNameTranslation',
        'plugin.javelin.EventsDescriptionTranslation',
        'plugin.javelin.EventTypes',
        'plugin.javelin.EventTypesNameTranslation',
        'plugin.javelin.Questionnaires',
        'plugin.javelin.QuestionnairesNameTranslation',
        'plugin.javelin.Questions',
        'plugin.javelin.QuestionsNameTranslation',
        'plugin.javelin.QuestionsQuestionTranslation',
        'plugin.javelin.Answers',
        'plugin.javelin.AnswersAnswerTranslation',
        'plugin.javelin.Responses',
        'plugin.javelin.Registrations',
        'plugin.javelin.Prices',
        'plugin.javelin.PricesNameTranslation',
        'plugin.javelin.PricesDescriptionTranslation',
        'plugin.javelin.Payments',
        'plugin.javelin.RegistrationAudits',
        'plugin.javelin.QuestionnairesQuestions',
        'plugin.javelin.Settings',
        'plugin.javelin.Skills',
        'plugin.javelin.Stats',
        'plugin.javelin.StatTypes',
        'plugin.javelin.StatTypesNameTranslation',
        'plugin.javelin.StatTypesAbbrTranslation',
        'plugin.javelin.Subscriptions',
        'plugin.javelin.MailingLists',
        'plugin.javelin.MailingListsNameTranslation',
        'plugin.javelin.Newsletters',
        'plugin.javelin.NewslettersNameTranslation',
        'plugin.javelin.NewslettersSubjectTranslation',
        'plugin.javelin.NewslettersTextTranslation',
        'plugin.javelin.Deliveries',
        'plugin.javelin.TaskSlots',
        'plugin.javelin.Tasks',
        'plugin.javelin.TasksNameTranslation',
        'plugin.javelin.TasksDescriptionTranslation',
        'plugin.javelin.TasksNotesTranslation',
        'plugin.javelin.Categories',
        'plugin.javelin.CategoriesNameTranslation',
        'plugin.javelin.ApprovedBy',
        'plugin.javelin.Uploads',
        'plugin.javelin.UploadTypes',
        'plugin.javelin.UploadTypesNameTranslation',
        'plugin.javelin.TeamsPeople',
        'plugin.javelin.AffiliatesPeople',
        'plugin.javelin.BadgesPeople',
        'plugin.javelin.NominatedBy',
        'plugin.javelin.DivisionsPeople',
        'plugin.javelin.Franchises',
        'plugin.javelin.FranchisesPeople',
        'plugin.javelin.FranchisesTeams',
        'plugin.javelin.Groups',
        'plugin.javelin.GroupsNameTranslation',
        'plugin.javelin.GroupsDescriptionTranslation',
        'plugin.javelin.GroupsPeople',
        'plugin.javelin.Waivers',
        'plugin.javelin.WaiversNameTranslation',
        'plugin.javelin.WaiversDescriptionTranslation',
        'plugin.javelin.WaiversTextTranslation',
        'plugin.javelin.WaiversPeople',
        'plugin.javelin.CreatedPerson',
        'plugin.javelin.DivisionsGameslots',
        'plugin.javelin.Pools',
        'plugin.javelin.PoolsNameTranslation',
        'plugin.javelin.PoolsTeams',
        'plugin.javelin.DependencyPool',
        'plugin.javelin.DependencyPoolNameTranslation',
        'plugin.javelin.HomeTeam',
        'plugin.javelin.HomePoolTeam',
        'plugin.javelin.AwayTeam',
        'plugin.javelin.AwayPoolTeam',
        'plugin.javelin.ScoreDetails',
        'plugin.javelin.ScoreDetailStats',
        'plugin.javelin.ScoreReminderEmails',
        'plugin.javelin.ScoreMismatchEmails',
        'plugin.javelin.Allstars',
        'plugin.javelin.Contacts',
        'plugin.javelin.ContactsNameTranslation',
        'plugin.javelin.Holidays',
        'plugin.javelin.HolidaysNameTranslation',
        'plugin.javelin.LeaguesStatTypes',
        'plugin.javelin.Days',
        'plugin.javelin.DaysNameTranslation',
        'plugin.javelin.DaysShortNameTranslation',
        'plugin.javelin.DivisionsDays'
    ];

	/**
	 * Test join method as an admin
	 *
	 * @return void
	 */
	public function testJoinAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a manager
	 *
	 * @return void
	 */
	public function testJoinAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a coordinator
	 *
	 * @return void
	 */
	public function testJoinAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a captain
	 *
	 * @return void
	 */
	public function testJoinAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a player
	 *
	 * @return void
	 */
	public function testJoinAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as someone else
	 *
	 * @return void
	 */
	public function testJoinAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method without being logged in
	 *
	 * @return void
	 */
	public function testJoinAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
