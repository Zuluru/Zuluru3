<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\Policy\Result;

class RedirectResult extends Result
{
	/**
	 * URL to redirect to.
	 */
	protected array $url;

	/**
	 * Element for rendering flash message.
	 */
	protected string $element;

	/**
	 * Options for rendering flash message.
	 */
	protected array $options;

	/**
	 * Constructor
	 *
	 * @param string|null $reason Failure reason.
	 * @param array $url URL to redirect to.
	 * @param string $element Optional element for rendering flash message.
	 * @param array $options Optional options for rendering flash message.
	 */
	public function __construct(?string $reason = null, array $url = [], string $element = 'info', array $options = []) {
		parent::__construct(false, $reason);
		$this->url = $url;
		$this->element = $element;
		$this->options = $options;
	}

	public function getUrl(): array {
		return $this->url;
	}

	public function getElement(): string {
		return $this->element;
	}

	public function getOptions(): array {
		return $this->options;
	}
}
