<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Migrations\AbstractMigration;

class RemoveObsoleteIntegrations extends AbstractMigration
{
    /**
     * Up Method.
     */
    public function up(): void
    {
		$this->table('people')
			->removeColumn('twitter_token')
			->removeColumn('twitter_secret')
			->update();

		$this->table('teams')
			->removeColumn('use_javelin')
			->removeColumn('twitter_user')
			->removeColumn('flickr_user')
			->removeColumn('flickr_set')
			->removeColumn('flickr_ban')
			->update();

		$this->execute('DELETE FROM plugins WHERE name = \'Javelin\'');
		$this->execute('DELETE FROM settings WHERE name = \'javelin\' OR category = \'javelin\' OR name = \'twitter\' OR category = \'twitter\' or name = \'flickr\'');
		Cache::delete('config', 'long_term');
    }
}
