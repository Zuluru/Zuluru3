<?php
namespace App\Http;

use App\Controller\RegistrationsController;

abstract class API {

	/**
	 * @var bool
	 */
	private $test;

	/**
	 * API constructor.
	 * @param bool $test
	 */
	public function __construct(bool $test) {
		$this->test = $test;
	}

	public function setTest(bool $test): void {
		$this->test = $test;
	}

	public function isTest(): bool {
		return $this->test;
	}

	public static function isTestData($data): bool {
		return RegistrationsController::isTest();
	}

	public static function splitRegistrationIds(string $ids): array {
		// Pull out any IDs that are for debits
		$registration_ids = $debit_ids = [];
		foreach (explode(',', $ids) as $id) {
			if ($id[0] === 'D') {
				$debit_ids[] = substr($id, 1);
			} else {
				$registration_ids[] = $id;
			}
		}

		return [$registration_ids, $debit_ids];
	}

}
