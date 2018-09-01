<?php
namespace App\Exception;

use Cake\Core\Exception\Exception;

class ScheduleException extends Exception {
	/**
	 * Array of message strings that are passed in from the constructor, and
	 * made available in the view when a development error is displayed.
	 *
	 * @var array
	 */
	protected $_messages = [];

    public function __construct($message, $params = null) {
        if (!$params) {
			$params = ['class' => 'info'];
		}
		if (is_array($message)) {
			parent::__construct(null);
			$this->_messages = $message;
		} else {
			parent::__construct($message);
		}
        $this->_attributes = $params;
    }

	public function getMessages() {
		if (!empty($this->_messages)) {
			return $this->_messages;
		} else {
			return $this->getMessage();
		}
	}

}
