<?php
/**
 * Emulates the email sending process for testing purposes
 */
namespace App\Mailer\Transport;

use Cake\Core\Configure;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Mailer\Email;
use Cake\View\View;

/**
 * Cli Transport class, used during development to display the email on the command line
 */
class CliTransport extends DebugTransport {

	/**
	 * Send mail
	 *
	 * @param \Cake\Mailer\Email $email Cake Email
	 * @return array
	 */
	public function send(Email $email) {
		$result = parent::send($email);
		$view = new View();
		$message = $view->element('Email/debug', [
			'saved' => true,
			'to' => $email->to(),
			'from' => $email->from(),
			'replyTo' => $email->replyTo(),
			'cc' => $email->cc(),
			'bcc' => $email->bcc(),
			'subject' => $email->subject(),
			'result' => $result,
		]);

		$messages = Configure::read('test_emails') ?: [];
		$messages[] = $message;
		Configure::write('test_emails', $messages);
	}
}
