<?php
namespace App\TestSuite;

use Cake\TestSuite\Constraint\Email\MailContains;
use Cake\TestSuite\Constraint\Email\MailContainsHtml;
use Cake\TestSuite\Constraint\Email\MailContainsText;
use Cake\TestSuite\Constraint\Email\MailSentFrom;
use Cake\TestSuite\Constraint\Email\MailSentTo;
use Cake\TestSuite\Constraint\Email\MailSentWith;
use Cake\TestSuite\EmailTrait;
use PHPUnit\Framework\Constraint\LogicalNot;

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
	public function assertMailNotSentToAt($at, $address, string $message = '')
	{
		$this->assertThat($address, new LogicalNot(new MailSentTo($at)), $message);
	}

	/**
	 * Asserts an email at a specific index was NOT sent from an address
	 *
	 * @param int $at Email index
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentFromAt($at, $address, string $message = '')
	{
		$this->assertThat($address, new LogicalNot(new MailSentFrom($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsAt($at, $contents, string $message = '')
	{
		$this->assertThat($contents, new LogicalNot(new MailContains($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected html contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsHtmlAt($at, $contents, string $message = '')
	{
		$this->assertThat($contents, new LogicalNot(new MailContainsHtml($at)), $message);
	}

	/**
	 * Asserts an email at a specific index does NOT contain expected text contents
	 *
	 * @param int $at Email index
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsTextAt($at, $contents, string $message = '')
	{
		$this->assertThat($contents, new LogicalNot(new MailContainsText($at)), $message);
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
	public function assertMailNotSentWithAt($at, $expected, $parameter, string $message = '')
	{
		$this->assertThat($expected, new LogicalNot(new MailSentWith($at, $parameter)), $message);
	}

	/**
	 * Asserts an email was NOT sent to an address
	 *
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentTo($address, string $message = '')
	{
		$this->assertThat($address, new LogicalNot(new MailSentTo()), $message);
	}

	/**
	 * Asserts an email was NOT sent from an address
	 *
	 * @param string $address Email address
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentFrom($address, string $message = '')
	{
		$this->assertThat($address, new LogicalNot(new MailSentFrom()), $message);
	}

	/**
	 * Asserts an email does NOT contain expected contents
	 *
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContains($contents, string $message = '')
	{
		$this->assertThat($contents, new LogicalNot(new MailContains()), $message);
	}

	/**
	 * Asserts an email does NOT contain expected html contents
	 *
	 * @param string $contents Contents
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotContainsHtml($contents, string $message = '')
	{
		$this->assertThat($contents, new LogicalNot(new MailContainsHtml()), $message);
	}

	/**
	 * Asserts an email does NOT contain an expected text content
	 *
	 * @param string $expectedText Expected text.
	 * @param string $message Message to display if assertion fails.
	 * @return void
	 */
	public function assertMailNotContainsText($expectedText, string $message = '')
	{
		$this->assertThat($expectedText, new LogicalNot(new MailContainsText()), $message);
	}

	/**
	 * Asserts an email does NOT contain the expected value within an Email getter
	 *
	 * @param string $expected Contents
	 * @param string $parameter Email getter parameter (e.g. "cc", "subject")
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailNotSentWith($expected, $parameter, string $message = '')
	{
		$this->assertThat($expected, new LogicalNot(new MailSentWith(null, $parameter)), $message);
	}

	/**
	 * Asserts an email contains the expected value within an Email getter
	 *
	 * @param array $expected Contents
	 * @param string $parameter Email getter parameter (e.g. "cc", "subject")
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailSentWithArray(array $expected, string $parameter, string $message = ''): void
	{
		$this->assertThat($expected, new MailSentWith(null, $parameter), $message);
	}

	/**
	 * Asserts an email at a specific index contains the expected value within an Email getter
	 *
	 * @param int $at Email index
	 * @param string $expected Contents
	 * @param string $parameter Email getter parameter (e.g. "cc", "bcc")
	 * @param string $message Message
	 * @return void
	 */
	public function assertMailSentWithArrayAt(int $at, array $expected, string $parameter, string $message = ''): void
	{
		$this->assertThat($expected, new MailSentWith($at, $parameter), $message);
	}
}
