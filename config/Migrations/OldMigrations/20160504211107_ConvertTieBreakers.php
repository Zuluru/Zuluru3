<?php
use Migrations\AbstractMigration;

define('TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS', 1);
define('TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT', 2);
define('TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS', 3);
define('TIE_BREAKER_PM_HTH_GF_LOSS', 4);
define('TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT', 5);
define('TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS', 6);
define('TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS', 7);
define('TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS_SPIRIT', 8);
define('TIE_BREAKER_CF_SPIRIT_HTH_HTHPM_PM_GF_LOSS', 9);
define('TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS', 10);
define('TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS_SPIRIT', 11);
define('TIE_BREAKER_SPIRIT_HTH_HTHPM_CF_PM_GF_LOSS', 12);
define('TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS', 13);
define('TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS_SPIRIT', 14);
define('TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_CF_LOSS', 15);
define('TIE_BREAKER_CF_PM_HTH_GF_LOSS', 16);
define('TIE_BREAKER_CF_PM_HTH_GF_LOSS_SPIRIT', 17);
define('TIE_BREAKER_SPIRIT_CF_PM_HTH_GF_LOSS', 18);
define('TIE_BREAKER_PM_CF_HTH_GF_LOSS', 19);
define('TIE_BREAKER_PM_CF_HTH_GF_LOSS_SPIRIT', 20);
define('TIE_BREAKER_SPIRIT_PM_CF_HTH_GF_LOSS', 21);
define('TIE_BREAKER_PM_HTH_CF_GF_LOSS', 22);
define('TIE_BREAKER_PM_HTH_CF_GF_LOSS_SPIRIT', 23);
define('TIE_BREAKER_SPIRIT_PM_HTH_CF_GF_LOSS', 24);

class ConvertTieBreakers extends AbstractMigration {

	private $tie_breakers = [
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => ['win', 'hth', 'hthpm', 'pm', 'gf', 'loss'],
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT => ['win', 'hth', 'hthpm', 'pm', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS => ['win', 'spirit', 'hth', 'hthpm', 'pm', 'gf', 'loss'],
		TIE_BREAKER_PM_HTH_GF_LOSS => ['win', 'pm', 'hth', 'gf', 'loss'],
		TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT => ['win', 'pm', 'hth', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS => ['win', 'spirit', 'pm', 'hth', 'gf', 'loss'],
		TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS => ['win', 'cf', 'hth', 'hthpm', 'pm', 'gf', 'loss'],
		TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS_SPIRIT => ['win', 'cf', 'hth', 'hthpm', 'pm', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_CF_SPIRIT_HTH_HTHPM_PM_GF_LOSS => ['win', 'cf', 'spirit', 'hth', 'hthpm', 'pm', 'gf', 'loss'],
		TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS => ['win', 'hth', 'hthpm', 'cf', 'pm', 'gf', 'loss'],
		TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS_SPIRIT => ['win', 'hth', 'hthpm', 'cf', 'pm', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_HTH_HTHPM_CF_PM_GF_LOSS => ['win', 'spirit', 'hth', 'hthpm', 'cf', 'pm', 'gf', 'loss'],
		TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS => ['win', 'hth', 'hthpm', 'pm', 'gf', 'cf', 'loss'],
		TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS_SPIRIT => ['win', 'hth', 'hthpm', 'pm', 'gf', 'cf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_CF_LOSS => ['win', 'spirit', 'hth', 'hthpm', 'pm', 'gf', 'cf', 'loss'],
		TIE_BREAKER_CF_PM_HTH_GF_LOSS => ['win', 'cf', 'pm', 'hth', 'gf', 'loss'],
		TIE_BREAKER_CF_PM_HTH_GF_LOSS_SPIRIT => ['win', 'cf', 'pm', 'hth', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_CF_PM_HTH_GF_LOSS => ['win', 'spirit', 'cf', 'pm', 'hth', 'gf', 'loss'],
		TIE_BREAKER_PM_CF_HTH_GF_LOSS => ['win', 'pm', 'cf', 'hth', 'gf', 'loss'],
		TIE_BREAKER_PM_CF_HTH_GF_LOSS_SPIRIT => ['win', 'pm', 'cf', 'hth', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_PM_CF_HTH_GF_LOSS => ['win', 'spirit', 'pm', 'cf', 'hth', 'gf', 'loss'],
		TIE_BREAKER_PM_HTH_CF_GF_LOSS => ['win', 'pm', 'hth', 'cf', 'gf', 'loss'],
		TIE_BREAKER_PM_HTH_CF_GF_LOSS_SPIRIT => ['win', 'pm', 'hth', 'cf', 'gf', 'loss', 'spirit'],
		TIE_BREAKER_SPIRIT_PM_HTH_CF_GF_LOSS => ['win', 'spirit', 'pm', 'hth', 'cf', 'gf', 'loss'],
	];

	/**
	 * Up Method.
	 *
	 * @return void
	 */
	public function up() {
		$this->table('leagues')
			->changeColumn('tie_breaker', 'string', ['length' => 100, 'null' => false])
			->save();

        foreach ($this->tie_breakers as $key => $tie_breaker) {
			$string = implode(',', $tie_breaker);
			$this->execute("UPDATE leagues SET tie_breaker = '$string' WHERE tie_breaker = $key");
		}
	}

	/**
	 * Down Method.
	 *
	 * @return void
	 */
	public function down() {
        foreach ($this->tie_breakers as $key => $tie_breaker) {
			$string = implode(',', $tie_breaker);
			$this->execute("UPDATE leagues SET tie_breaker = $key WHERE tie_breaker = '$string'");
        }

		$this->table('leagues')
			->changeColumn('tie_breaker', 'integer', ['null' => false, 'default' => 1])
			->save();
	}
}
