<?php
use Migrations\AbstractMigration;

class AddPaymentToCredits extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('payments')
			->addColumn('payment_id', 'integer', ['null' => true, 'after' => 'registration_audit_id'])
			->update();

		$this->execute('UPDATE payments p, payments r SET r.payment_id = p.id WHERE p.registration_id = r.registration_id AND p.refunded_amount = -r.payment_amount');

		$this->table('credits')
			->addColumn('payment_id', 'integer', ['null' => true, 'after' => 'person_id'])
			->update();

		$this->execute('UPDATE payments p, credits c, registrations r SET c.payment_id = p.id WHERE p.registration_id = r.id AND p.payment_amount = -c.amount AND p.created = c.created');
	}
}
