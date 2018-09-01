<?php
/**
 * Emulates the email sending process for testing purposes
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Mailer\Transport;

use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Mailer\Email;

/**
 * Flash Transport class, used during development to display the email in a flash message
 */
class FlashTransport extends DebugTransport {

	/**
	 * Send mail
	 *
	 * @param \Cake\Mailer\Email $email Cake Email
	 * @return array
	 */
	public function send(Email $email) {
		$result = parent::send($email);
		$event = new CakeEvent('Mailer.Transport.flash', $this, [$email, $result]);
		EventManager::instance()->dispatch($event);
	}
}
