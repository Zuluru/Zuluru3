<?php
declare(strict_types=1);

namespace App\View;

use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Calendar\View\IcalView as View;

/**
 * A view class that is used for ICAL responses.
 * Currently only switches the default layout and sets the response type - which just maps to
 * text/html by default.
 */
class IcalView extends View
{
    /**
     * @inheritDoc
     */
    protected $layout = 'ical';

	/**
	 * @inheritDoc
	 */
	public function __construct(
		?ServerRequest $request = null,
		?Response $response = null,
		?EventManager $eventManager = null,
		array $viewOptions = []
	) {
		parent::__construct($request, $response, $eventManager, $viewOptions);

		$this->enableAutoLayout();
	}

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
		$this->loadHelper('Time', ['className' => 'ZuluruTime']);
        parent::initialize();
    }
}
