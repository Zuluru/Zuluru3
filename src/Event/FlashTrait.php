<?php
namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventManager;

trait FlashTrait {

	// Simple helper function to simulate normal flash message usage for non-controllers
	public static function Flash($element, $message, $options = []) {
		$e = new Event('Flash', null, [$element, $message, $options]);
		EventManager::instance()->dispatch($e);
	}

}
