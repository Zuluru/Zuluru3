<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class AddPluginsTable extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('plugins')
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('load_name', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('path', 'string', [
				'default' => null,
				'limit' => 256,
				'null' => false,
			])
			->addColumn('advertise', 'boolean', [
				'default' => true,
				'null' => false,
			])
			->addColumn('enabled', 'boolean', [
				'default' => false,
				'null' => false,
			])
			->create();

		$migrations = new Migrations();
		$migrations->seed(['seed' => 'PluginsSeed']);

		$settings = \Cake\ORM\TableRegistry::getTableLocator()->get('Settings');

		$payment = $settings->find()->where(['category' => 'payment', 'name' => 'payment_implementation'])->first();
		if ($payment) {
			if ($payment->value == 'chase') {
				$this->execute('UPDATE plugins SET enabled = 1 WHERE load_name = \'Chase\'');
			} else if ($payment->value == 'paypal') {
				$this->execute('UPDATE plugins SET enabled = 1 WHERE load_name = \'PayPal\'');
			}
			$settings->delete($payment);
		}

		$javelin = $settings->find()->where(['category' => 'plugin', 'name' => 'Javelin'])->first();
		if ($javelin) {
			if ($javelin->value == 1) {
				$this->execute('UPDATE plugins SET enabled = 1 WHERE load_name = \'Javelin\'');
			}
			$settings->delete($javelin);
		}
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$settings = \Cake\ORM\TableRegistry::getTableLocator()->get('Settings');
		foreach (\Cake\ORM\TableRegistry::getTableLocator()->get('Plugins')->find()->where(['enabled' => true]) as $plugin) {
			switch($plugin->load_name) {
				case 'Chase':
					$settings->save($settings->newEntity([
						'category' => 'payment',
						'name' => 'payment_implementation',
						'value' => 'chase',
					]));
					break;

				case 'PayPal':
					$settings->save($settings->newEntity([
						'category' => 'payment',
						'name' => 'payment_implementation',
						'value' => 'paypal',
					]));
					break;

				case 'Javelin':
					$settings->save($settings->newEntity([
						'category' => 'plugin',
						'name' => 'Javelin',
						'value' => '1',
					]));
					break;
			}
		}

		$this->table('plugins')
			->drop();
	}
}
