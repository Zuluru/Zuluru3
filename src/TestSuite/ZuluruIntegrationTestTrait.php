<?php
declare(strict_types=1);

namespace App\TestSuite;

use Cake\TestSuite\Constraint\Session\FlashParamEquals;

trait ZuluruIntegrationTestTrait
{
	public function assertFlashMessages(array $expected, string $key = 'flash', string $message = ''): void{
		$verboseMessage = $this->extractVerboseMessage($message);
		$this->assertThat($expected, new FlashParamEquals($this->_requestSession, $key, 'message'), $verboseMessage);
	}
}
