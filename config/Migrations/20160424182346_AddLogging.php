<?php
use Migrations\AbstractMigration;

class AddLogging extends AbstractMigration {
    /**
     * Change Method.
     *
     * @return void
     */
    public function change() {
		$logs = $this->table('logs');
		$logs->addColumn('person_id', 'integer', ['null' => true, 'default' => null])
			->addColumn('login_id', 'integer', ['null' => true, 'default' => null])
			->addColumn('affiliate_id', 'integer', ['null' => true, 'default' => null])
			->addColumn('controller', 'string', ['limit' => 64])
			->addColumn('action', 'string', ['limit' => 64])
			->addColumn('query', 'text', ['null' => true, 'default' => null])
			->addColumn('params', 'text', ['null' => true, 'default' => null])
			->addColumn('form', 'text', ['null' => true, 'default' => null])
			->addColumn('memory', 'integer')
			->addColumn('ms', 'integer')
			->addColumn('created', 'datetime')
			->save();
    }
}
