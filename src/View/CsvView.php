<?php
declare(strict_types=1);

namespace App\View;

class CsvView extends AppView {
	/**
	 * @inheritDoc
	 */
	protected $layoutPath = 'csv';

	/**
	 * @inheritDoc
	 */
	protected $subDir = 'csv';

	/**
	 * @inheritDoc
	 */
	public function initialize(): void {
		parent::initialize();
		$this->setResponse($this->getResponse()->withType('csv'));
	}
}
