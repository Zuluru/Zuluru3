<?php
namespace App\Http;

use App\Controller\RegistrationsController;
use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;

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

	public function canRefund(Payment $payment): bool {
		return false;
	}

	public function refund(Event $event, Payment $payment, Payment $refund): array {
		throw new PaymentException('Payment providers must implement their own refund capability.');
	}
}
