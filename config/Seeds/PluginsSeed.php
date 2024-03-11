<?php
use Migrations\AbstractSeed;

/**
 * Plugins seed.
 */
class PluginsSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => 'PayPal',
				'load_name' => 'PayPalPayment',
				'path' => 'plugins/PayPalPayment',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'name' => 'Chase Paymentech',
				'load_name' => 'ChasePayment',
				'path' => 'plugins/ChasePayment',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'name' => 'Stripe',
				'load_name' => 'StripePayment',
				'path' => 'plugins/StripePayment',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'name' => 'Elavon',
				'load_name' => 'ElavonPayment',
				'path' => 'plugins/ElavonPayment',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'name' => 'Javelin',
				'load_name' => 'Javelin',
				'path' => 'plugins/Javelin',
				'advertise' => true,
				'enabled' => false,
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('plugins');
		$table->insert($this->data())->save();
	}
}
