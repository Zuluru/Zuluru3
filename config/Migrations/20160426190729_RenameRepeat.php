<?php
use Migrations\AbstractMigration;

class RenameRepeat extends AbstractMigration {
    /**
     * Change Method.
     *
     * @return void
     */
    public function change() {
		$this->table('notices')
			->renameColumn('repeat', 'repeat_on')
			->save();
    }
}
