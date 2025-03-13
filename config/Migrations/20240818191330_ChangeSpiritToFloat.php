<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ChangeSpiritToFloat extends AbstractMigration
{
    /**
     * Up Method.
     */
    public function up(): void {
		$this->table('spirit_entries')
			->changeColumn('entered_sotg', 'float', [
				'default' => '0.0',
				'null' => true,
				'precision' => 4,
				'scale' => 1,
			])
			->changeColumn('q1', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q2', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q3', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q4', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q5', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q6', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q7', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q8', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q9', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->changeColumn('q10', 'float', [
				'default' => '0.0',
				'null' => false,
				'precision' => 3,
				'scale' => 1,
			])
			->update();
    }

    /**
     * Down Method.
     */
    public function down(): void {
		$this->table('spirit_entries')
			->changeColumn('entered_sotg', 'integer', [
				'default' => '0',
				'null' => true,
			])
			->changeColumn('q1', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q2', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q3', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q4', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q5', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q6', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q7', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q8', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q9', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->changeColumn('q10', 'integer', [
				'default' => '0',
				'null' => false,
			])
			->update();
    }
}
