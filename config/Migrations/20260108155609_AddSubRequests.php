<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Db\Table\Column;

class AddSubRequests extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
		$this->table('sub_requests')
			->addColumn('captain_id', Column::INTEGER, [
				'default' => null,
				'null' => false,
			])
			->addColumn('team_id', Column::INTEGER, [
				'default' => null,
				'null' => false,
			])
			->addColumn('game_date', Column::DATE, [
				'default' => null,
				'null' => true,
			])
			->addColumn('person_id', Column::INTEGER, [
				'default' => null,
				'null' => false,
			])
			->addIndex(['captain_id'])
			->addIndex(['team_id'])
			->addIndex(['game_date'])
			->create();
    }
}
