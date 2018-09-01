<?php
namespace App\Model\Table;

/**
 * ActivityLogs Model
 */
class ActivityLogsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('activity_logs');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');
	}

}
