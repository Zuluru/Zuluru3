<?php
use Migrations\AbstractMigration;

class AddPlacement extends AbstractMigration {
    /**
     * Change Method.
     *
     * @return void
     */
    public function change() {
        $this->table('games')
            ->addColumn('placement', 'integer', ['null' => true, 'default' => null, 'after' => 'name'])
            ->save();

		for ($place = 1; $place < 100; ++ $place) {
			// Can't use Number::ordinal, because that returns Unicode characters that don't match the database
			$ends = ['th','st','nd','rd','th','th','th','th','th','th'];
			if (($place % 100) >= 11 && ($place % 100) <= 13) {
				$ordinal = $place . 'th';
			} else {
				$ordinal = $place . $ends[$place % 10];
			}

			$this->execute("UPDATE games SET placement = $place, name = NULL WHERE name LIKE '%-$ordinal' OR name LIKE '% $ordinal' OR name LIKE '$ordinal%'");
		}
    }
}
