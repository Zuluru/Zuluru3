<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Registration;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\EventFactory;
use App\Test\Factory\PaymentFactory;
use App\Test\Factory\RegistrationFactory;
use App\Test\Factory\ResponseFactory;
use App\Test\Factory\TeamFactory;
use Cake\Core\Configure;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class DiverseRegistrationsScenario implements FixtureScenarioInterface {

	// Constants to make finding the relevant registrations reliable
	public static $MEMBERSHIP = 0;
	public static $TEAM = 1;
	public static $INDIVIDUAL = 2;

	/**
	 * @param ...$args
	 * Possible arguments are:
	 * - affiliate Affiliate
	 * - coordinator Person
	 * - member Person
	 * - captain Person
	 * - player Person
	 * - membershipPayment string
	 * - teamPayment string
	 * - individualPayment string
	 * @return \App\Model\Entity\Registration[]
	 */
	public function load(...$args) {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$affiliate_id = isset($args['affiliate']) ? $args['affiliate']->id : 1;

		$registrations = [];

		if (!empty($args['member'])) {
			// Create the membership event and registration
			$event = EventFactory::make(['name' => 'Membership', 'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP, 'affiliate_id' => $affiliate_id])
				->with('Prices', ['cost' => 10, 'tax1' => 1.50])
				->persist();

			$registrations[self::$MEMBERSHIP] = RegistrationFactory::make([
				'payment' => $args['membershipPayment'] ?? 'Unpaid',
				'total_amount' => 11.50,
			])
				->with('People', $args['member'])
				->with('Events', $event)
				->with('Prices', $event->prices[0])
				->persist();
		}

		if (!empty($args['captain']) || !empty($args['player'])) {
			// Make a division to connect to the team and individual events
			$division = DivisionFactory::make([])
				->with('Leagues', ['affiliate_id' => $affiliate_id])
				->persist();
			if (array_key_exists('coordinator', $args)) {
				DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $division->id])->persist();
			}
		}

		if (!empty($args['captain'])) {
			// Create the team event and registration and maybe a team record if it's paid
			$event = EventFactory::make(['name' => 'Team', 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES, 'affiliate_id' => $affiliate_id, 'division_id' => $division->id])
				->with('Prices', ['cost' => 1000, 'tax1' => 150])
				->persist();

			$team_registration = $registrations[self::$TEAM] = RegistrationFactory::make([
				'payment' => $args['teamPayment'] ?? 'Unpaid',
				'total_amount' => 1150,
			])
				->with('People', $args['captain'])
				->with('Events', $event)
				->with('Prices', $event->prices[0])
				->persist();

			if (in_array($team_registration->payment, Configure::read('registration_some_paid'))) {
				$team = TeamFactory::make(['division_id' => $division->id, 'affiliate_id' => $affiliate_id])->persist();
				$team_registration->responses = [
					ResponseFactory::make([
						'event_id' => $event->id,
						'registration_id' => $team_registration->id,
						'question_id' => TEAM_ID_CREATED,
						'answer_text' => $team->id,
					])
						->persist()
				];
			}
		}

		if (!empty($args['player'])) {
			// Create the individual event and registration
			$event = EventFactory::make(['name' => 'Individual', 'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $affiliate_id, 'division_id' => $division->id])
				->with('Prices', ['cost' => 100, 'tax1' => 15])
				->persist();

			$registrations[self::$INDIVIDUAL] = RegistrationFactory::make([
				'payment' => $args['individualPayment'] ?? 'Unpaid',
				'total_amount' => 115,
			])
				->with('People', $args['player'] ?? null)
				->with('Events', $event)
				->with('Prices', $event->prices[0])
				->persist();
		}

		foreach ($registrations as $registration) {
			$this->addPayment($registration);
		}

		return $registrations;
	}

	public function addPayment(Registration $registration) {
		switch ($registration->payment) {
			case 'Paid':
				$amount = $registration->total_amount;
				$type = 'Full';
				break;

			case 'Deposit':
			case 'Partial':
				$amount = round($registration->total_amount / 2, 2);
				$type = 'Deposit';
				break;

			default:
				return;
		}

		$registration->payments = [
			PaymentFactory::make([
				'registration_id' => $registration->id,
				'payment_amount' => $amount,
				'payment_type' => $type,
			])
				->persist()
		];
	}
}
