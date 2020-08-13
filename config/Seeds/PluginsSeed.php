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
				'id' => 1,
				'name' => 'PayPal',
				'load_name' => 'PayPal',
				'path' => 'plugins/PayPal',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'id' => 2,
				'name' => 'Chase Paymentech',
				'load_name' => 'Chase',
				'path' => 'plugins/Chase',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'id' => 3,
				'name' => 'Stripe',
				'load_name' => 'Stripe',
				'path' => 'plugins/Stripe',
				'advertise' => true,
				'enabled' => false,
			],
			[
				'id' => 4,
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
