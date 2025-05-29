<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameRank extends AbstractMigration
{
	/**
	 * Change Method.
	 */
	public function change(): void
	{
		$this->table('teams_facilities')
			->renameColumn('rank', 'ranking')
			->update();
	}
}
