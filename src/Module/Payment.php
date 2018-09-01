<?php
/**
 * Base class for payment provider functionality.
 */
namespace App\Module;

use Cake\Core\Configure;
use Cake\Network\Request;
use App\Core\UserCache;

abstract class Payment {
	public $can_refund = false;

	public function process(Request $request, $checkHash = true) {
		return __('Payment processor does not have a "process" function defined!');
	}

	public function processData(Array $data, $checkHash = true) {
		return __('Payment processor does not have a "processData" function defined!');
	}

	public function parseEmail($text) {
		return __('Payment processor does not have a "parseEmail" function defined!');
	}

	public function isTest() {
		$test_config = Configure::read('payment.test_payments');
		switch ($test_config) {
			case 1:
				return true;

			case 2:
				// TODO: Better way to do this
				$groups = UserCache::getInstance()->read('Groups');
				return collection($groups)->some(function ($group) {
					return in_array($group->id, [GROUP_ADMIN, GROUP_MANAGER]);
				});

			default:
				return false;
		}
	}
}
