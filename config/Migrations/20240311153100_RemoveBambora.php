<?php
use Migrations\AbstractMigration;
use Migrations\Migrations;

class RemoveBambora extends AbstractMigration {
	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$plugins = \Cake\ORM\TableRegistry::getTableLocator()->get('Plugins');

		$plugins->deleteAll(['name' => 'Bambora']);
	}
}
