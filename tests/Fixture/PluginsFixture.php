<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PluginsFixture
 *
 */
class PluginsFixture extends TestFixture {

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'name' => 'Chase Paymentech',
			'load_name' => 'ChasePayment',
			'path' => 'plugins/ChasePayment',
			'advertise' => true,
			'enabled' => true,
		],
		[
			'name' => 'PayPal',
			'load_name' => 'PayPalPayment',
			'path' => 'plugins/PayPalPayment',
			'advertise' => true,
			'enabled' => true,
		],
		[
			'name' => 'Elavon',
			'load_name' => 'ElavonPayment',
			'path' => 'plugins/ElavonPayment',
			'advertise' => true,
			'enabled' => true,
		],
		[
			'name' => 'Stripe',
			'load_name' => 'StripePayment',
			'path' => 'plugins/StripePayment',
			'advertise' => true,
			'enabled' => true,
		],
	];

}
