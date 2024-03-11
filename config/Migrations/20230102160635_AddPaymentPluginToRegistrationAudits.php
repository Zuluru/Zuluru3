<?php
use Migrations\AbstractMigration;

class AddPaymentPluginToRegistrationAudits extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('registration_audits')
			->addColumn('payment_plugin', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => true,
			])
			->changeColumn('transaction_id', 'string', [
				'default' => null,
				'limit' => 64,
				'null' => true,
			])
			->update();
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
		$this->table('registration_audits')
			->removeColumn('payment_plugin')
			->changeColumn('transaction_id', 'string', [
				'default' => null,
				'limit' => 18,
				'null' => true,
			])
			->update();
	}
}
