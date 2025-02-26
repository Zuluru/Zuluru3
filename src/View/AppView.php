<?php
namespace App\View;

use App\View\Helper\AuthorizeHelper;
use App\View\Helper\SelectorHelper;
use App\View\Helper\UserCacheHelper;
use App\View\Helper\ZuluruBreadcrumbsHelper;
use App\View\Helper\ZuluruFormHelper;
use App\View\Helper\ZuluruGameHelper;
use App\View\Helper\ZuluruHtmlHelper;
use App\View\Helper\ZuluruTimeHelper;
use Authentication\View\Helper\IdentityHelper;
use BootstrapUI\View\Helper\PaginatorHelper;
use BootstrapUI\View\UIViewTrait;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\View\Helper\NumberHelper;
use Cake\View\Helper\TextHelper;
use Cake\View\View;
use ZuluruBootstrap\View\Helper\BootstrapHelper;
use ZuluruJquery\View\Helper\JqueryHelper;

/**
 * App View class
 *
 * @property IdentityHelper $Identity
 * @property AuthorizeHelper $Authorize
 * @property UserCacheHelper $UserCache
 * @property NumberHelper $Number
 * @property TextHelper $Text
 * @property SelectorHelper $Selector
 * @property ZuluruHtmlHelper $Html
 * @property ZuluruFormHelper $Form
 * @property ZuluruBreadcrumbsHelper $Breadcrumbs
 * @property ZuluruTimeHelper $Time
 * @property ZuluruGameHelper $Game
 * @property PaginatorHelper $Paginator
 * @property BootstrapHelper $Bootstrap
 * @property JqueryHelper $Jquery
 */
class AppView extends View {

	use UIViewTrait;

	public function __construct(?ServerRequest $request = null, ?Response $response = null, ?EventManager $eventManager = null, array $viewOptions = [])
	{
		parent::__construct($request, $response, $eventManager, $viewOptions);

		// The default "fade" class that Bootstrap wants to use conflicts with other things sometimes, making flash messages invisible.
		// Can't do this with default configuration in the initialize function, because that merges provided config with the default,
		// which allows adding more classes, but not removing default ones.
		$this->Flash->setConfig('class', ['alert', 'alert-dismissible', 'show', 'd-flex', 'align-items-center'], false);
	}

	/**
	 * Initialization hook method.
	 */
	public function initialize(): void {
		parent::initialize();

		$this->helpers = [
			'Identity' => ['className' => 'Authentication.Identity'],
			'Authorize',
			'UserCache',
			'Number',
			'Text',
			'Selector',
			'Html' => ['className' => 'ZuluruHtml'],
			'Form' => ['className' => 'ZuluruForm'],
			'Breadcrumbs' => ['className' => 'ZuluruBreadcrumbs'],
			'Time' => ['className' => 'ZuluruTime'],
			'Game' => ['className' => 'ZuluruGame'],
			'Paginator' => ['className' => 'BootstrapUI.Paginator', 'config' => ['templates' => 'paginator-templates']],
			'Bootstrap' => ['className' => 'ZuluruBootstrap.Bootstrap'],
			'Jquery' => ['className' => 'ZuluruJquery.Jquery'],
			//'Less' => ['className' => 'Less.Less'], // required for parsing less files
		];

		// Call the initializeUI method from UIViewTrait
		$this->initializeUI(['layout' => false]);
	}

}
