<?php
namespace App\TestSuite;

use Cake\TestSuite\Constraint\Email\MailContains;
use Cake\TestSuite\Constraint\Email\MailContainsHtml;
use Cake\TestSuite\Constraint\Email\MailContainsText;
use Cake\TestSuite\Constraint\Email\MailSentFrom;
use Cake\TestSuite\Constraint\Email\MailSentTo;
use Cake\TestSuite\Constraint\Email\MailSentWith;
use Cake\TestSuite\EmailTrait;
use PHPUnit_Framework_Constraint_Not;

trait ZuluruEmailTrait {
	use EmailTrait;

	/**
	 * Asserts an email at a specific index was NOT sent to an address
	 *
	 * @param int $at Email index
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentToAt($at, $address, $message = null)
	{
		$this->assertThat($address, new PHPUnit_Framework_Constraint_Not(new MailSentTo($at)), $message);
	}

	/**
	 * Asserts an email at a specific index was NOT sent from an address
	 *
	 * @param int $at Email index
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentFromAt($at, $address, $message = null)
	{
		$this->assertThat($address, new PHPUnit_Framework_Constraint_Not(new MailSentFrom($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsAt($at, $contents, $message = null)
	{
		$this->assertThat($contents, new PHPUnit_Framework_Constraint_Not(new MailContains($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected html contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsHtmlAt($at, $contents, $message = null)
	{
		$this->assertThat($contents, new PHPUnit_Framework_Constraint_Not(new MailContainsHtml($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected text contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsTextAt($at, $contents, $message = null)
	{
		$this->assertThat($contents, new PHPUnit_Framework_Constraint_Not(new MailContainsText($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain the expected value within an Email getter
	 *
	 * @param int $at Email index
	 * @param string $expected Contents
	 * @param string $parameter Email getter parameter (e.g. "cc", "subject")
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentWithAt($at, $expected, $parameter, $message = null)
	{
		$this->assertThat($expected, new PHPUnit_Framework_Constraint_Not(new MailSentWith($at, $parameter)), $message);
	}

	/**
	 * Asserts an email was NOT sent to an address
	 *
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentTo($address, $message = null)
	{
		$this->assertThat($address, new PHPUnit_Framework_Constraint_Not(new MailSentTo()), $message);
	}

	/**
	 * Asserts an email was NOT sent from an address
	 *
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentFrom($address, $message = null)
	{
		$this->assertThat($address, new PHPUnit_Framework_Constraint_Not(new MailSentFrom()), $message);
	}

	/**
	 * Asserts an email does NOT contain expected contents
	 *
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContains($contents, $message = null)
	{
		$this->assertThat($contents, new PHPUnit_Framework_Constraint_Not(new MailContains()), $message);
	}

	/**
	 * Asserts an email does NOT contain expected html contents
	 *
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsHtml($contents, $message = null)
	{
		$this->assertThat($contents, new PHPUnit_Framework_Constraint_Not(new MailContainsHtml()), $message);
	}

	/**
	 * Asserts an email does NOT contain an expected text content
	 *
	 * @param string $expectedText Expected text.
	 * @param string $message Message to display if assertion fails.
	 * @return void
	 */
	public function assertMailNotContainsText($expectedText, $message = null)
	{
		$this->assertThat($expectedText, new PHPUnit_Framework_Constraint_Not(new MailContainsText()), $message);
	}

	/**
	 * Asserts an email does NOT contain the expected value within an Email getter
	 *
	 * @param string $expected Contents
	 * @param string $parameter Email getter parameter (e.g. "cc", "subject")
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentWith($expected, $parameter, $message = null)
	{
		$this->assertThat($expected, new PHPUnit_Framework_Constraint_Not(new MailSentWith(null, $parameter)), $message);
	}
}