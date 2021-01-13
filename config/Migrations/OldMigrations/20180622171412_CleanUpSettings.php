<?php
use Migrations\AbstractMigration;

class CleanUpSettings extends AbstractMigration {
    /**
     * Up Method.
     *
     * @return void
     */
    public function up() {
		$this->execute('DELETE FROM settings WHERE name IN (\'pdfize\', \'emogrifier\', \'willing_to_volunteer\') OR (category = \'organization\' AND name like \'%_start\')');
    }
}
