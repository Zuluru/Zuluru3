<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddOfficialIdToGames extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        $this->table('games_officials')
			->addColumn('game_id', \Phinx\Db\Table\Column::INTEGER, [
				'default' => null,
				'null' => false,
			])
			->addColumn('official_id', \Phinx\Db\Table\Column::INTEGER, [
				'default' => null,
				'null' => false,
			])
			->addIndex(['game_id'])
			->addIndex(['official_id'])
        	->create();
    }
}
