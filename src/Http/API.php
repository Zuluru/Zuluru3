<?php
namespace App\Http;

abstract class API {

	/**
	 * @var bool
	 */
	private $test = false;

	/**
	 * API constructor.
	 * @param bool $test
	 */
	public function __construct(bool $test) {
		$this->test = $test;
	}

	/**
	 * @param bool $test
	 */
	public function setTest(bool $test) {
		$this->test = $test;
	}

	/**
	 * @return bool
	 */
	public function isTest() {
		return $this->test;
	}

}
