<?php
namespace App\Exception;

use Authorization\Exception\ForbiddenException;

/**
 * Used when a policy wants to specify the redirect URL and/or a custom message.
 */
class ForbiddenRedirectException extends ForbiddenException {

	private $_url;
	private $_class;
	private $_options;

	public function __construct($message, $url = [], $class = 'info', $options = []) {
		parent::__construct($message);
		$this->_url = $url;
		$this->_class = $class;
		$this->_options = $options;
    }

	public function getUrl() {
		return $this->_url;
    }

	public function getClass() {
		return $this->_class;
    }

	public function getOptions() {
		return $this->_options;
    }

}
