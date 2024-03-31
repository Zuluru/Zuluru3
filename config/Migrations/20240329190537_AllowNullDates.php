<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AllowNullDates extends AbstractMigration
{
    public function up(): void
    {
        $this->table('people')
            ->changeColumn('birthdate', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();

        $this->execute('UPDATE people SET birthdate = NULL WHERE birthdate = \'0000-00-00\'');

        $this->table('leagues')
            ->changeColumn('open', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->changeColumn('close', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();

        $this->execute('UPDATE leagues SET open = NULL WHERE open = \'0000-00-00\'');
        $this->execute('UPDATE leagues SET close = NULL WHERE close = \'0000-00-00\'');

        $this->table('divisions')
            ->changeColumn('open', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->changeColumn('close', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();

        $this->execute('UPDATE divisions SET open = NULL WHERE open = \'0000-00-00\'');
        $this->execute('UPDATE divisions SET close = NULL WHERE close = \'0000-00-00\'');
    }

    public function down(): void
    {
        $this->execute('UPDATE people SET birthdate = \'0000-00-00\' WHERE birthdate = NULL');

        $this->table('people')
            ->changeColumn('birthdate', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->update();

        $this->execute('UPDATE leagues SET open = \'0000-00-00\' WHERE open = NULL');
        $this->execute('UPDATE leagues SET close = \'0000-00-00\' WHERE close = NULL');

        $this->table('leagues')
            ->changeColumn('open', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->changeColumn('close', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->update();

        $this->execute('UPDATE divisions SET open = \'0000-00-00\' WHERE open = NULL');
        $this->execute('UPDATE divisions SET close = \'0000-00-00\' WHERE close = NULL');

        $this->table('divisions')
            ->changeColumn('open', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->changeColumn('close', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->update();
    }
}
